import React from 'react';
import _ from "lodash";
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import './stylels.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {setValue} from "../../../../store/survey/actions";
import {Button} from "../../../common/ui/Button";
import {normalizeText} from "../../../../helpers";
import Text from "../../../common/inputs/Text";

const INJURY_KNEE         = 'knee';
const INJURY_SHOULDERS    = 'shoulders';
const INJURY_ALBOW        = 'albow';
const INJURY_WRIST        = 'wrist';
const INJURY_BACK         = 'back';
const INJURY_LOWER_BACK   = 'lowerBack';
const INJURY_ABDOMINALS   = 'abdominals';
const INJURY_OTHER        = 'other';


const INJURY_OPTIONS = [
  INJURY_KNEE,
  INJURY_SHOULDERS,
  INJURY_ALBOW,
  INJURY_WRIST,
  INJURY_BACK,
  INJURY_LOWER_BACK,
  INJURY_ABDOMINALS,
  INJURY_OTHER,
];

const AdditionalInfo = (props) => {
  const {t} = useTranslation('main');

  const {
    injuries,
    comment,
    setInjuries = () => null,
    setComment = () => null
  } = props;

  const handleInjury = (option) => {
    if(injuries.find(item => item.value === option.value)) {
      return setInjuries(injuries.filter(item => item.value !== option.value));
    }
    return setInjuries([...injuries, option]);
  };

  const handleCommentChange = (value) => {
    const normalized = normalizeText(value);
    setComment(normalized)
  };

  const injuryOptions = INJURY_OPTIONS.map((value) => {
    const name = t(`additionalInfo.injuries.options.${value}`);
    const selected = !!injuries.find(injury => injury.value === value);
    return { name, value, selected };
  });

  return (
    <Section id={'additional-info'}>
      <SectionHeader>
        <SectionTitle>
          {t('additionalInfo.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <br/>
        <br/>
        <h6>{t('additionalInfo.injuries.title')}</h6>
        <div className={'extra-parts'}>
          {
            injuryOptions.map((option, i) => (
              <Button
                key={i}
                variant={'primary'}
                inverse={!option.selected}
                onClick={() => handleInjury(option)}
              >
                {option.name}
              </Button>
            ))
          }
        </div>
        <br/>
        <br/>
        <div className="sub-wrapper">
          <h6>{t('additionalInfo.comment.title')}</h6>
          <Text
            label={t('additionalInfo.comment.label')}
            value={comment}
            multiline
            onChange={(comment) => handleCommentChange(comment)}
          />
        </div>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  injuries: _.get(state.survey.data, 'injuries', []),
  comment: _.get(state.survey.data, 'other', ''),
});

const mapDispatchToProps = dispatch => ({
  setInjuries: (value) => dispatch(setValue('injuries', value)),
  setComment: (value) => dispatch(setValue('other', value)),
});

export default connect(mapStateToProps, mapDispatchToProps)(AdditionalInfo);