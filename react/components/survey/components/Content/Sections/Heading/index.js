import './styles.scss';
import React from 'react';
import {connect} from "react-redux";
import {
  Section,
  Body as SectionBody,
  Title as SectionTitle
} from "../../../common/ui/Section";
import {useTranslation} from "react-i18next";

const DEFAULT_BG = '/bundles/app/images/survey-page/bg.jpg';


const Heading = ({background}) => {
  const {t} = useTranslation('main');
  const bgUrl = background ? background : DEFAULT_BG;
  return (
    <Section id={'heading'} style={{backgroundImage: `url(${bgUrl})`}}>
      <div className="overlay" />
      <SectionBody>
        <SectionTitle>
          {t('heading.title')}
        </SectionTitle>
        <br/>
        <p>{t('heading.subtitle')}</p>
      </SectionBody>
    </Section>
  )
};
const mapStateToProps = state => ({
  background: _.get(state.config, 'background', undefined)
});
export default connect(mapStateToProps)(Heading);