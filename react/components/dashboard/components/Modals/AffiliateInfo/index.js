import React from 'react';
import './styles.css'
import { Modal, ModalBody, CloseButton} from "react-bootstrap";
import Avatar from "react-avatar";

const AffiliateInfoModal = ({ show, onHide }) => {
  return (
    <Modal id={'affiliate-terms'} show={show} onHide={onHide}>
      <ModalBody>
        <CloseButton onClick={onHide} label={'x'}/>
        <p className={'title'}>
          <strong>
            Invite your Trainer friends to join Zenfit and earn $$$.
          </strong>
        </p>
        <p className={'added-by'}>Added by <a href="#">Lasse Stokholm</a>, March 11, 2020</p>
        <div className={'author-info-block'}>
          <p className={'text-uppercase author'}>LASSE STOKHOLM</p>
          <div>
            <Avatar size={32} round={true} name={'Lasse Stokholm'} src={'/bundles/app/images/affiliate-author.jpg'} />
            <span>Zenfit CEO</span>
          </div>
        </div>
        <hr/>
        <div className={'content'}>
          <p>
            <strong>
              Earn up to $400 per trainer you refer to Zenfit! 💸
            </strong>
          </p>
          <p>
            After 5 years in business we have experienced that some of the best coaches we get onboard Zenfit are the ones recommended by you.
          </p>
          <p>
            That’s why we have now implemented this highly profitable commission setup as a small sign of gratitude for helping the Zenfit community grow!
          </p>
          <p>
            <strong>
              How it works
            </strong>
          </p>
          <p>
            Unlike other software providers, we don’t give out lame commissions! We give you what a referral is truly worth!
          </p>
          <ul>
            <li>
              <strong>1 trainer referral in one month:</strong> Refer one trainer in one month and earn $200 in cash commission!
            </li>
            <li>
              <strong>2 trainer referrals in one month:</strong> Refer two trainers in the same month and we will pay you $300 for the second trainer! (earn total $500)
            </li>
            <li>
              <strong>3 trainer referrals or more in one month:</strong> Refer three trainers or more in the same month and we will pay you $400 per additional trainer! (earn from $900 and up)
            </li>
          </ul>
          <p>
            To get started, just get your unique referral link from the Dashboard page, and off you go!
          </p>
          <p>
            <strong>
              Terms
            </strong>
          </p>
          <p>
            Our terms are simple: The trainer you refer need to become an active member and use your unique referral link.
            The coach need to stay the full commitment period (typically 3 months). You won’t earn commission from website sales,
            marketing subscriptions or any other additional revenue generated by the trainer.
            You won’t be credited for the referral, if we are already currently in touch with the particular trainer.
          </p>
          <p>
            We don’t have any payout limits, but if you earn more than $2,000 / month, we will change your payouts to quarterly.
          </p>
        </div>
      </ModalBody>
    </Modal>
  );
};

export default AffiliateInfoModal;