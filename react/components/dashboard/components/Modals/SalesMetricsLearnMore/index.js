import React from 'react';
import './styles.css'
import { Modal, ModalBody, CloseButton} from "react-bootstrap";
import ReactPlayer from 'react-player'

const SalesMetricsLearnMore = ({ show, onHide }) => {
  return (
    <Modal id={'sales-metrics-info'} show={show} onHide={onHide}>
      <ModalBody style={{padding: 0}}>
        <div style={{padding: '10px 15px'}}>
          <CloseButton onClick={onHide} label={'x'}/>
        </div>
        <ReactPlayer
          width={'100%'}
          controls
          url={'https://vimeo.com/395942896/ddfff400b8'}
        />
      </ModalBody>
    </Modal>
  );
};

export default SalesMetricsLearnMore;
