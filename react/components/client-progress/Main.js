import React, {
  Fragment,
  useCallback,
  useEffect,
  useRef,
  useState,
} from 'react'
import {connect} from 'react-redux';
import WorkoutTrackingSection from './components/WorkoutTrackingSection'
import KcalTrackingSection from './components/KcalTrackingSection'

import {
  getStats,
  getExercisesStats,
  setMfpIntegrationInfo,
  getExerciseStats,
  setClientId,
  getAllExercise
} from './store/stats/actions';

const Main = ({ initial, setClientId, getStats, getExercisesStats, getExerciseStats, getSavedWorkoutsStats, setMfpIntegrationInfo, exercisesData }) => {
  const {
    clientId,
    mfpLink
  } = initial;

  const [filters, setFilters] = useState({
    limit: 20,
    offset: 0,
  });

  const [isSending, setIsSending] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const isMounted = useRef(true);

  useEffect(() => {
    return () => {
      isMounted.current = false
    }
  }, []);

  let type;

  const handleOpen = useCallback( async (exerciseId) => {
    if (isSending) return;
    setIsSending(true);
    type = 'exercise';
    await getExerciseStats({clientId, type, exerciseId});

    if (isMounted.current) {
      setIsSending(false);
    }

  }, [isSending]);

  const handleLoadMore = useCallback(() => {
    if (hasMore) {
      setFilters({ ...filters, offset: exercisesData.length });
    }
  }, [hasMore]);

  useEffect(() => {
    setHasMore(filters.limit === exercisesData.length);
  }, [exercisesData]);

  useEffect(() => {
    getStats(clientId, moment().startOf('isoWeek').format('YYYY-MM-DD'), moment().endOf('isoWeek').format('YYYY-MM-DD'));
    setMfpIntegrationInfo(mfpLink);
    setClientId(clientId)
  }, [clientId]);

  useEffect(() => {
    type = 'exercises';
    getExercisesStats({clientId, filters, type});
  }, [filters]);

  return (
    <Fragment>
      <WorkoutTrackingSection
        loadMore={handleLoadMore}
        hasMore={hasMore}
        handleOpen={handleOpen}
        clientId={clientId}
      />
      <KcalTrackingSection />
    </Fragment>
  )
};

const mapStateToProps = state => {
  const exercisesData = state.stats.exercises || [];
  return {
    exercisesData
  }
};

const mapDispatchToProps = dispatch => ({
  getExercisesStats: (({clientId, filters, type }) => dispatch(getExercisesStats({clientId, filters, type }))),
  getExerciseStats: (({clientId, exerciseId, type }) => dispatch(getExerciseStats({clientId, type, exerciseId }))),
  getSavedWorkoutsStats: (({clientId, period, type}) => dispatch(getSavedWorkoutsStats({clientId, period, type}))),
  getStats: (clientId, from, to) => dispatch(getStats(clientId, from, to)),
  setMfpIntegrationInfo: (mfpLink) => dispatch(setMfpIntegrationInfo(mfpLink)),
  setClientId: (id) => dispatch(setClientId(id))
});

export default connect(mapStateToProps, mapDispatchToProps)(Main);
