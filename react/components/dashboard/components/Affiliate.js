import React, { Fragment, useState, useRef } from 'react';
import {CopyToClipboard} from 'react-copy-to-clipboard';
import {transformToMoney} from "../helpers";
import AffiliateInfo from './Modals/AffiliateInfo';

let timer = null;

const Affiliate = ({ link, earnings, onRequestLink = () => null, loadingLink }) => {
  const inputRef = useRef(null);
  const copyBtnRef = useRef(null);
  const [copied, setCopied] = useState(false);
  const [showModal, setShowModal] = useState(false);

  const handleCopyClick = () => {
    clearTimeout(timer);
    setCopied(true);
    timer = setTimeout(() => setCopied(false), 5000);
  };

  const handleRequestClick = () => {
    onRequestLink()
      .then(() => {
        if(copyBtnRef && copyBtnRef.current) {
          copyBtnRef.current.click();
        }
      })
  };

  return (
    <Fragment>
      <div className={'affiliate-promotion text-dark'}>
        <div style={{marginBottom: '7px', marginTop: '24px'}}>
          <strong className={'fs-18 fw-600'}>
            Invite your Trainer friends to join Zenfit and earn cool cash! 💸
          </strong>
        </div>
        {
          link ?
            (
              <Fragment>
                <div className={'text-muted'} style={{margin: '5px 0'}}>
                  <input ref={inputRef} name="referralLink" type="text" disabled value={link} />
                </div>
                <div style={{margin: '5px 0'}}>
                  <CopyToClipboard text={link} onCopy={handleCopyClick}>
                    <button ref={copyBtnRef} className={'affiliate-action'} onClick={handleCopyClick}>
                      {
                        !!copied ? (
                          <Fragment>
                            <i className={'fa fa-check'} />
                            <span>Copied</span>
                          </Fragment>
                        ) :
                        <span>Copy your unique link</span>
                      }
                    </button>
                  </CopyToClipboard>
                </div>
                <span className={'text-muted fs-12 fw-normal'}>Read more about our <a href={'#'} onClick={() => setShowModal(true)}>Terms</a></span>
              </Fragment>
            ) :
            (
              <div>
                <button className={'affiliate-action'} onClick={handleRequestClick} disabled={loadingLink}>
                  {!!loadingLink && <i className={'fa fa-spinner fa-spin'} />}
                  <span>Get your unique referral link</span>
                </button>
              </div>
            )
        }
        <div style={{marginTop: 'auto', marginBottom: '15px'}}>
          <div className={'earnings-info'}>
            <p className={'text-uppercase fs-12 fw-500'}>
              Your Earnings
            </p>
            <p className={'text-uppercase font-bold fs-md'}>
              {transformToMoney(earnings || 0, '$')}
            </p>
          </div>
        </div>
      </div>
      <AffiliateInfo show={showModal} onHide={() => setShowModal(false)}/>
    </Fragment>
  );
};

export default Affiliate;
