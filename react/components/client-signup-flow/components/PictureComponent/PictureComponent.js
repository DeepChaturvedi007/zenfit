import React, {Fragment, useRef, useState} from 'react';
import {ZFPictureStyled} from "./ZFPictureStyled";
import {FileDrop} from "react-file-drop";
import {useTranslation} from "react-i18next";
import {CloseRounded} from "@material-ui/icons";

const PictureComponent = ({side, title, type, onChange, value, onRemove}) => {
    const {t} = useTranslation('globalMessages');
    const isPictureNoteActive = (value == "" || value == null);
    const ref = useRef();

    const handleReset = () => {
        ref.current.value = ''
        onRemove('')
    }

    return (
        <ZFPictureStyled className={!isPictureNoteActive && 'active'}>
            {
                isPictureNoteActive
                    ? (
                        <Fragment>
                            <label htmlFor={`input_${type}`} className={'default'}>
                                <FileDrop onDrop={(files, event) => onChange(files[0])}>
                                    <img className={"default"} src={side} alt="sideImage"/>
                                </FileDrop>
                            </label>
                            <div className="description">
                                <h2>{title}</h2>
                                <h3>{t('client.activation.bodyPictures.spec')}</h3>
                                <label htmlFor={`input_${type}`}><span>{t('client.activation.bodyPictures.uploadPicture')}</span></label>
                            </div>
                        </Fragment>
                    )
                    : (
                        <div className="image">
                            <span
                                onClick={() => handleReset()}
                            >
                                <CloseRounded/>
                            </span>
                            <img className={"active"} src={(typeof value) == "string" ? value : URL.createObjectURL(value)} alt=""/>
                        </div>
                    )
            }
            <input ref={ref} accept="image/*" id={`input_${type}`} type="file" onChange={e => onChange(e.target.files[0])} />
        </ZFPictureStyled>
    )
}

export default PictureComponent;


