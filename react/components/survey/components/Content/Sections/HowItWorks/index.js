import './styles.scss';
import React from 'react';
import {
  Section,
  Body as SectionBody,
  Title as SectionTitle,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {useTranslation} from "react-i18next";
import {Col, Row} from "../../../../../shared/components/Grid";

const IMG_1 = 'data:image/png;base64,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';
const IMG_2 = 'data:image/png;base64,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';
const IMG_3 = 'data:image/png;base64,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';

const HowItWorks = () => {
  const {t} = useTranslation('main');
  return (
    <Section id={'how-it-works'}>
      <SectionHeader>
        <SectionTitle>
          {t('howItWorks.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <Row className={'j-between'}>
          <Col size={3}>
            <div className={'step one'}>
              <img src={IMG_1} alt="list" />
              <h5>{t('howItWorks.steps.1.title')}</h5>
              <p>{t('howItWorks.steps.1.description')}</p>
            </div>
          </Col>
          <Col size={3}>
            <div className={'step two'}>
              <img src={IMG_2} alt="list" />
              <h5>{t('howItWorks.steps.2.title')}</h5>
              <p>{t('howItWorks.steps.2.description')}</p>
            </div>
          </Col>
          <Col size={3}>
            <div className={'step three'}>
              <img src={IMG_3} alt="list" />
              <h5>{t('howItWorks.steps.3.title')}</h5>
              <p>{t('howItWorks.steps.3.description')}</p>
            </div>
          </Col>
        </Row>
      </SectionBody>
    </Section>
  )
};

export default HowItWorks;