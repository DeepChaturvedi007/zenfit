import React, { useCallback, useState } from 'react'
import {connect} from "react-redux";

import {
  Section,
  Title as SectionTitle,
  Body as SectionBody
} from "../../../shared/components/Section";
import {
  Row,
  Col
} from "../../../shared/components/Grid";

import CommonStatsCard from './Cards/CommonStatsCard';
import WorkoutPlanCard from '../WorkoutTrackingSection/Cards/WorkoutPlanCard';
import ExerciseStatsCard from './Cards/ExerciseStatsCard';
import ExerciseGraphCard from './Cards/ExerciseGraphCard';
import WorkoutHistoryCard from './Cards/WorkoutHistoryCard';
import { Preloader } from '../../../shared/components/Common'

const WorkoutTrackingSection = ({ hasMore, handleOpen, loadMore, hasClient, loading, clientId}) => {
  const [open, setOpen] = useState(false);
  const toggle = useCallback(
    () => setOpen(open => !open),
    []
  );

  const handleShow = (bool) => {
    setOpen(bool);
  }

  return (
    <Section>
      <SectionTitle>Workout Tracking</SectionTitle>
      <SectionBody>
      {
        !loading && hasClient ?
          (
            <Row>
              <Col>
                <Row>
                  <Col size={6} mode={'lg'} className={'col-md-4'}>
                    <CommonStatsCard />
                  </Col>
                  <Col size={6} mode={'lg'} className={'col-md-4'}>
                    <WorkoutPlanCard />
                  </Col>
                </Row>
                <Row>
                  <Col size={4} mode={'lg'} className={'col-md-6'}>
                    <WorkoutHistoryCard
                      clientId={clientId}
                    />
                  </Col>
                  <Col size={(open) ? 4 : 4} mode={'lg'} className={'col-md-4'}>
                    <ExerciseStatsCard
                      handleOpen={handleOpen}
                      handleShow={handleShow}
                      toggle={toggle}
                      loadMore={loadMore}
                      hasMore={hasMore}
                    />
                  </Col>
                  {
                    open && (
                      <Col size={4} mode={'lg'} className={'col-md-6'}>
                        <ExerciseGraphCard />
                      </Col>
                    )
                  }
                </Row>
              </Col>
            </Row>
          ) :
          <Preloader />
      }
      </SectionBody>
    </Section>
  )
};

const mapStateToProps = state => {
  return {
    loading: state.stats.loading,
    hasClient: !!state.macros.clientId,
  }
};

export default connect(mapStateToProps)(WorkoutTrackingSection);
