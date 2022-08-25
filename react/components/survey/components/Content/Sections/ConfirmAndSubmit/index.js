import React, {useState} from 'react';
import './stylels.scss';
import {
  Section,
  Body as SectionBody,
} from "../../../common/ui/Section";
import {Button} from "../../../common/ui/Button";
import {useTranslation} from "react-i18next";
import _ from "lodash";
import { submitData } from "../../../../store/survey/actions";
import {connect} from "react-redux";
import TermsAndConditionsModal from '../../../Modals/TermsAndConditions';
import Checkbox from '../../../common/inputs/Checkbox';

const validate = (data) => {
  return true;
};

const ConfirmAndSubmit = (props) => {
  const {t} = useTranslation('main');
  const {t: tMessages} = useTranslation('messages');

  const { data, submit, error } = props;

  const [agree, setAgree] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [healthy, setHealthy] = useState(false);

  const handleSubmit = () => {
    const isValid = validate(data);
    isValid && submit(data);
  };

  const handleModalOpen = (e) => {
    e.preventDefault();
    setShowModal(true);
  };
  const errorMessage = _.isPlainObject(error) ? error.message : error;
  return (
    <Section id={'confirm-and-submit'}>
      <SectionBody>
        {
          !!errorMessage &&
          <p className={'text-danger text-center fs-16 m-15'}>{tMessages(errorMessage)}</p>
        }
        <div className="checkbox">
          <label>
            <Checkbox
              checked={healthy}
              onChange={(isChecked) => setHealthy(isChecked)}
            />
            {t('confirmation.rules.label')}
          </label>
        </div>
        <div className="checkbox">
          <label>
            <Checkbox
              checked={agree}
              onChange={(isChecked) => setAgree(isChecked)}
            />
            <span>
              {t('confirmation.terms.label')}&nbsp;
              <a href="#" onClick={handleModalOpen}>{t('confirmation.terms.link')}</a>
            </span>
          </label>
        </div>
        <br/>
        <Button
          disabled={!healthy || !agree}
          variant={'default'}
          inverse
          onClick={handleSubmit}
        >
          {t('confirmation.submit.text')}
        </Button>
      </SectionBody>
      <TermsAndConditionsModal show={showModal} onHide={() => setShowModal(false)}/>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  data: _.get(state.survey, 'data', null),
  error: _.get(state.survey, 'error', null)
});

const mapDispatchToProps = dispatch => ({
  submit: data => dispatch(submitData(data))
});


export default connect(mapStateToProps, mapDispatchToProps)(ConfirmAndSubmit);