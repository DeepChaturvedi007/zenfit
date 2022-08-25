import React, {useState} from 'react';
import {
  Section,
  Header as SectionHeader,
  Title as SectionTitle,
  Body as SectionBody
} from "../../shared/components/Section";
import Guide from "./Guide";
import PlansSummary from "./PlansSummary";

const Content = () => {
  const [showGuide, setShowGide] = useState(true);
  return (
    <Section id="plans-summary" className={'w-100'}>
      <SectionHeader>
        <SectionTitle>
          Plans
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        { showGuide && <Guide onClose={() => setShowGide(false)}/>}
        <PlansSummary />
      </SectionBody>
    </Section>
  );
};

export default Content;