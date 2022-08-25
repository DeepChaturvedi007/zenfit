import _ from "lodash";

export const FieldValidators = (fields, translator, fieldsIgnored = []) => {
    let errors = {};

    /*
    * 0 val = options
    * '' val = field
    * [] = multi select
    *
    * */

    Object.entries(fields).map(field => {
        if ( field[0] == "passwordConfirm") {
            field[1] !== fields["password"] && (errors[field[0]] = translator('client.activation.passwordDontMatch'))
        }

        if (field[0] == 'password') {
            !(field[1].length >= 6) && (errors[field[0]] = translator('client.activation.passwordLength'))
        }

        if (field[0] == "email") {
            const isItEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            const email = field[1]
            !isItEmail.test(email) && (errors[field[0]] = translator('client.activation.emailInvalid'))
        }

        if (field[0] === 'phone') {
            field[1].length <= 3 && (errors[field[0]] = translator('client.activation.fieldEmpty'))
        }

        !fieldsIgnored.includes(field[0]) && (
            /*input field*/
            field[1].length <= 0 && (errors[field[0]] = translator('client.activation.fieldEmpty')),
                /*select field*/
            field[1] === 0 && (errors[field[0]] = translator('client.activation.fieldNotSelected'))
        )
    })

    if (Object.keys(errors).length !== 0) {
        throw errors
    }
}

export const prepareOptions = options => {
    return Object.keys(options).map(key => {
        return {value: key, label: _.upperFirst(options[key])}
    })
}

export const GET_IMAGE_ASSET = (relativePath) => {
    try {
        ImageStringValidator(relativePath)
        return `/bundles/app/images/${relativePath}`;
    } catch (e) {
        console.error(e);
    }
}


export const ImageStringValidator = (image) => {
    if(typeof image !== 'string'){
        throw new Error('Image is not a string')
    }
    if(!image.match(/\w+\.(jpg|jpeg|gif|json|png|tiff|bmp)$/gi)){
        throw new Error('Image is not marked with an image extension')
    }
}
