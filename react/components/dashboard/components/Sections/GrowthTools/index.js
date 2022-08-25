import React from 'react';
import './styles.css'
//import AffiliateCard from "./Cards/AffiliateCard";
import VideoCard from "./Cards/VideoCard";
import MembersOnlyCard from "./Cards/MembersOnlyCard";
import NewsCard from "./Cards/NewsCard";

import Section, {
  Header as SectionHeader,
  Title as SectionTitle,
  Body as SectionBody
} from "../../../../shared/components/Section";
import {Col, Row} from "../../../../shared/components/Grid";

const GrowthTools = () => {
  return (
    <Section>
      <SectionHeader>
        <SectionTitle>
          Growth tools
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <Row>
          <Col size={5}>
            <VideoCard />
          </Col>
          <Col size={7}>
            <Row>
              <Col size={6}>
                <MembersOnlyCard />
              </Col>
              <Col size={6}>
                <NewsCard />
              </Col>
            </Row>
          </Col>
        </Row>
      </SectionBody>
    </Section>
  );
};

export default GrowthTools;
