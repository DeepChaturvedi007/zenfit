import React, {useState, useEffect,} from 'react';
import { Col, Row } from "../../../shared/components/Grid";
import ClientTasks from './Sections/ClientTask';
import ClientInfo from './Sections/ClientInfo';
import ClientNotes from './Sections/ClientNotes';
import ClientProgression from './Sections/ClientProgression';
import ClientCheckIn from './Sections/ClientCheckIn';
import ClientPayment from './Sections/ClientPayment';
import ClientPicture from './Sections/ClientPicture';
import ClientWeight from './Sections/ClientWeight';
import ClientWorkouts from './Sections/ClientWorkouts';
import ClientMeals from './Sections/ClientMeals';
import ClientDocs from "./Sections/ClientDocs";
import ClientVideos from "./Sections/ClientVideos";

import * as clients from "../../store/clients/actions";
import { mergeValues, updateObject, prepareMeasuringValue } from '../../helpers';
import { CLIENT_INFO_FIELDS, CLIENT_NOTES_FIELDS } from '../../const.js';
import './styles.scss';
import { connect } from 'react-redux';

const ClientDetail = (props) => {
  const { selectedClientId, clientDetail, selectedClient, updateClientInfo, handleMessageType, openConfirmModal, handleActionModal } = props;
  const [clientInfo, setClientInfo] = useState(null);
  const [graphExpand, setGraphExpand] = useState(false)
  const [submit, setSubmit] = useState(false);
  let clientInfoFields = CLIENT_INFO_FIELDS;
  let clientNotesFields = CLIENT_NOTES_FIELDS;

  const prepareMeasures = (data, measuringSystem) => {
    let clientInfoData = data;

    const startVal = Object.values(clientInfoFields).find(item => item.key === "info.startWeight").value
    clientInfoData = updateObject(clientInfoData, 'info.startWeight', prepareMeasuringValue(startVal, measuringSystem, 'weight'));

    const goalVal = Object.values(clientInfoFields).find(item => item.key === "info.goalWeight").value
    clientInfoData = updateObject(clientInfoData, 'info.goalWeight', prepareMeasuringValue(goalVal, measuringSystem, 'weight'));

    /*const height = Object.values(clientInfoFields).find(item => item.key === "info.height").value
    clientInfoData = updateObject(clientInfoData, 'info.height', prepareMeasuringValue(height, measuringSystem, 'height'));*/

    return clientInfoData;
  };

  const prepareDates = (data, value, name) => {
    let clientInfoData = data;
    let startDate;
    let duration;

    if (name === 'duration') {
      startDate = Object.values(clientNotesFields).find(item => item.key === "startDate.date").value;
      duration = value;
    } else {
      startDate = value;
      duration = Object.values(clientNotesFields).find(item => item.key === "duration").value;
    }

    const endDate = moment(startDate).add(duration, 'months').format('Y-MM-DD');
    updateObject(clientInfoData, 'endDate', {date: endDate});

    return clientInfoData;
  }

  const changeClientInfo = (value, name) => {
    let data = updateObject(JSON.parse(JSON.stringify(clientInfo)), name, value);

    if (name === 'measuringSystem') {
      data = prepareMeasures(data, value);
    } else if (name === 'startDate.date' || name === 'duration') {
      data = prepareDates(data, value, name);
    }

    setClientInfo(data);
    setSubmit(true);
  };

  useEffect(() => {
    if (selectedClient) {
      setClientInfo(selectedClient);
    }
  }, [selectedClient]);

  useEffect(() => {
    mergeValues(clientInfoFields, clientInfo);
    mergeValues(clientNotesFields, clientInfo);

    if (submit) {
      updateClientInfo(selectedClient.id, clientInfo);
      setSubmit(false);
    }
  }, [clientInfo])

  const handleExpandProgressGraph = () => {
    setGraphExpand(!graphExpand)
  }

  return (
    <div>
      <div className="client-detail-title">
        <span>PowerTool</span>
        <div style={{ flex: 1 }} />
      </div>
      <Row className='client-expand-grid'>
        <Col size={3} order={graphExpand ? 3 : 1}>
          <ClientWeight measuringSystem={selectedClient ? selectedClient.measuringSystem : 1} />
          <br />
          <ClientPicture clientId={clientDetail.id} />
        </Col>
        <Col size={graphExpand ? 12 : 6} order={ graphExpand ? 1 : 2}>
          <ClientProgression
            clientGoalWeight={clientDetail.goalWeight}
            measuringSystem={selectedClient ? selectedClient.measuringSystem : 1}
            graphExpand={graphExpand}
            handleExpand={handleExpandProgressGraph}
          />
          <br />
          <ClientCheckIn />
        </Col>
        <Col size={3} order={graphExpand ? 2 : 3}>
          <ClientTasks
            clientDetail={clientDetail}
            selectedClientId={selectedClient ? selectedClient.id : null}
            handleMessageType={handleMessageType}
            openConfirmModal={openConfirmModal}
          />
          <br />
          <ClientPayment
            clientDetail={clientDetail}
            handleActionModal={handleActionModal}
          />
        </Col>
      </Row>
      <Row className='client-expand-grid'>
        <Col size={6}>
          <ClientInfo
            clientId={clientDetail.id}
            measuringSystem={selectedClient ? selectedClient.measuringSystem : 1}
            changeClientInfo={changeClientInfo}
            fields={clientInfoFields}
            selectedClient={selectedClient}
          />
        </Col>
        <Col size={6}>
          <ClientNotes
            clientDetail={clientDetail}
            changeClientInfo={changeClientInfo}
            fields={clientNotesFields}
          />
        </Col>
      </Row>
      <Row className='client-expand-grid'>
        <Col size={12}>
          <ClientWorkouts
            clientId={clientDetail.id}
            WorkoutPlansCount={clientDetail.workout_plans_count}
            handleActionModal={handleActionModal}
          />
        </Col>
      </Row>
      <Row className='client-expand-grid'>
        <Col size={12}>
          <ClientMeals
            clientId={clientDetail.id}
            masterMealPlansCount={clientDetail.master_meal_plans_count}
          />
        </Col>
      </Row>
      <Row className='client-expand-grid'>
        <Col size={6}>
          <ClientVideos
            clientId={clientDetail.id}
            videosCount={clientDetail.videos_count}
            handleActionModal={handleActionModal}
          />
        </Col>
        <Col size={6}>
          <ClientDocs
            clientId={clientDetail.id}
            documentsCount={clientDetail.documents_count}
            handleActionModal={handleActionModal}
          />
        </Col>
      </Row>
    </div>
  )
}

function mapStateToProps({ clients }) {
  return { selectedClient: clients.selectedClient };
}

export default connect(mapStateToProps, { ...clients })(ClientDetail);
