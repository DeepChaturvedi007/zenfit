import React from 'react';
import ZFButton from "../../../../shared/UI/Button";
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';
import ErrorOutlineIcon from '@material-ui/icons/ErrorOutline';

const SaveButton = (props) => {
    const { status, onClickSave } = props;
  
    switch (status) {
      case 'Save':
        return (
            <ZFButton color="primary" onClick={onClickSave}>
                {status}
            </ZFButton>
        )
      case 'Saved':
        return (
            <ZFButton onClick={onClickSave} color="saved-icon">
                <CheckCircleOutlineIcon />{status}
            </ZFButton>
        )
      case 'Error':
        return (
            <ZFButton onClick={onClickSave} color="error-icon">
                <ErrorOutlineIcon />{status}
            </ZFButton>
        )
      case 'Saving':
        return (
            <ZFButton onClick={onClickSave}>
                {status}
            </ZFButton>
        )
      default:
        return (
            <ZFButton color="primary" onClick={onClickSave}>
                {status}
            </ZFButton>
        )
    }
  }
export default SaveButton;

