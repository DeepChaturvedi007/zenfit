const TYPE_CLIENT = 'client';
const TYPE_TRAINER = 'trainer';

class StripeSCA {
  constructor(stripeSDK, type, params = {}) {
    this.stripe = stripeSDK;
    this.type = type;
    this.params = params;
    this.setParams(params);
  }

  setParams(params = {}) {
    for (let prop in params) {
      if (params.hasOwnProperty(prop)) {
        this.params[prop] = params[prop];
      }
    }
  }

  setToken(token) {
    this.params.token = token;
    return this;
  }

  setCard(card) {
    this.params.card = card;
    return this;
  }

  setFormData(formData) {
    this.params.formData = formData;
    return this;
  }

  setPaymentType(paymentType) {
    this.params.paymentType = paymentType;
    return this;
  }

  setSource(source) {
    this.params.source = source;
    return this;
  }

  setIban(iban) {
    this.params.iban = iban;
    return this;
  }

  initiateKlarna(amount, currency, country, description) {
    let that = this;
    return new Promise((resolve, reject) => {
      const redirectUrl = window.location.href + '?klarna_callback=1';
      that.stripe.createSource({
        type: 'klarna',
        flow: 'redirect',
        redirect: {
          return_url: redirectUrl
        },
        amount: amount,
        currency: currency,
        klarna: {
          product: 'payment',
          purchase_country: country,
          purchase_type: 'subscribe'
        },
        source_order: {
          items: [{
            type: 'sku',
            description: description,
            quantity: 1,
            currency: currency,
            amount: amount
          }],
        },
      }).then(function(res) {
        if (res.error) {
          return reject(new Error(res.error.message));
        }

        const source = res.source;
        const nameList = Object.keys(source.klarna).filter((item) => {
          return item.includes('_name');
        });
        const response = nameList.map((item) => {
          return {
            name: source.klarna[item],
            redirectUrl: source.klarna[item.replace('_name', "_redirect_url")]
          }
        });

        resolve(response);
      });
    });
  }

  handleRequest(url, data, token) {
    return $.ajax({
      url: url,
      type: 'POST',
      data: data,
      dataType: 'json',
      contentType: 'application/x-www-form-urlencoded',
      beforeSend: function (xhr) {
        xhr.setRequestHeader('Authorization', token);
      }
    });
  }

  getPaymentMethod({ client_secret }) {
    let stripeFunction;
    let options = {
      billing_details: {
        name: this.params.formData.name,
        email: this.params.formData.email
      }
    };

    if (this.params.paymentType === 'card') {
      options = Object.assign(options, {card: this.params.card});
      stripeFunction = this.stripe.confirmCardSetup(client_secret, {payment_method: options});
    } else if (this.params.paymentType === 'sepa') {
      options = Object.assign(options, {sepa_debit: this.params.iban});
      stripeFunction = this.stripe.confirmSepaDebitSetup(client_secret, {payment_method: options});
    }

    return new Promise((resolve, reject) => {
      stripeFunction.then(result => {
        if (result.error) {
          return reject(new Error(result.error.message));
        } else {
          let payment_method_id = result.setupIntent.payment_method;
          resolve(payment_method_id);
        }
      });
    });
  }

  initiateStripeSubscription(callback) {
    let that = this;
    //create new customer
    let body;
    if (this.type === TYPE_CLIENT) {
      body = {
        email: this.params.formData.email,
        name: this.params.formData.name,
        payment_type: this.params.paymentType,
        client: this.params.client,
        bundle: this.params.bundle,
        datakey: this.params.datakey
      }
    } else {
      body = {
        tax_exempt: this.params.tax_exempt,
        tax_id: this.params.tax_id
      }
    }

    this.handleRequest(this.params.initiateUrl, body, this.params.token)
      .done(res => {
        let data;
        if (this.type === TYPE_CLIENT) {
          data = Object.assign(this.params.formData, {
            customer: res.customer, //from initiateUrl request
            datakey: res.datakey, //from initiateUrl request
            bundle: this.params.bundle,
            client: res.client, //from initiateUrl request
            name: this.params.formData.name,
            email: this.params.formData.email,
            payment_type: this.params.paymentType
          });
        } else {
          data = {
            trial: this.params.trial,
            tax_rate: this.params.tax_rate,
            customer: res.customer //from initiateUrl request
          };
        }

        if (that.params.paymentType === 'klarna') {
          //payment is done using klarna
          const source = this.params.source;
          that.handleRequest(
            that.params.confirmUrl,
            Object.assign(data, {source}),
            that.params.token
          )
          .done(response => {
            that.handleConfirmSetup(response, callback);
          })
          .error(err => {
            callback(err.responseJSON);
          });
        } else {
          that.getPaymentMethod(res)
            .then(payment_method_id => {
              that.handleRequest(
                that.params.confirmUrl,
                Object.assign(data, {payment_method_id}),
                that.params.token
              )
              .done(response => {
                that.handleConfirmSetup(response, callback);
              })
              .error(err => {
                callback(err.responseJSON.error);
              })
            })
            .catch(err => {
              callback(err);
            })
        }

      });
  }

  handleConfirmSetup({ clientSecret, datakey, id, status, customer, bundle }, callback) {
    let that = this;

    let body;
    if (this.type === TYPE_CLIENT) {
      body = {
        datakey: datakey,
        bundle: bundle
      };
    } else {
      body = {
        subscription: id,
        customer: customer
      };
    }

    if (status === 'incomplete') {
      this.stripe.confirmCardPayment(clientSecret, {})
        .then(function (result) {
          if (result.error) {
            // Display error.message in your UI.
            // throw exception if error
            throw result.error;
          } else {
            // The setup has succeeded. Display a success message.
            that.handleSuccessRequest(callback, body);
          }
        })
        .catch(err => {
          callback(err);
        });
    } else {
      this.handleSuccessRequest(callback, body);
    }
  }

  handleSuccessRequest(callback, body) {
    this.handleRequest(this.params.confirmationUrl, body, this.params.token)
      .done(res => {
        window.location.replace(res.redirect);
      })
      .fail(err => {
        //callback if error
        callback(err.esponseJSON.error);
      });
  }

}

module.exports = StripeSCA;
