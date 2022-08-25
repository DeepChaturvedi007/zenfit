import React, {useEffect, useState} from 'react';
import { connect } from 'react-redux';
import * as signup from '../../store/signup/action';
import PictureComponent from "../PictureComponent/PictureComponent";
import {useTranslation} from "react-i18next";
import ZFButton from "../../../../shared/UI/Button";

const Pictures = ({step, saveFieldsAction, picturesState}) => {
    const {t} = useTranslation('globalMessages');
    const [pictures,setPictures] = useState({
        front: '',
        back: '',
        side: ''
    })

    useEffect(() => {
        Object.keys(picturesState).length > 0 && setPictures(Object.assign({...pictures}, picturesState));
    },[picturesState])

    const handleChange = (name,value) => {
        setPictures({ ...pictures, [name]: value })
    }

    const handleSave = () => {
        saveFieldsAction(pictures, parseInt(step) + 1, 'photos')
    }

    return (
        <div className={"zf-Progress"}>
            <div className="pictures">
                <PictureComponent
                    title={t('client.activation.bodyPictures.front')}
                    value={pictures.front}
                    type={'front'}
                    onChange={(val) => handleChange('front', val)}
                    onRemove={(val) => handleChange('front', val)}
                    side={'/bundles/app/images/client-signup-flow/BodyFront.png'}
                />
                <PictureComponent
                    title={t('client.activation.bodyPictures.back')}
                    value={pictures.back}
                    type={'back'}
                    onChange={(val) => handleChange('back', val)}
                    onRemove={(val) => handleChange('back', val)}
                    side={'/bundles/app/images/client-signup-flow/BodyBack.png'}
                />
                <PictureComponent
                    title={t('client.activation.bodyPictures.side')}
                    value={pictures.side}
                    type={'side'}
                    onChange={(val) => handleChange('side', val)}
                    onRemove={(val) => handleChange('side', val)}
                    side={'/bundles/app/images/client-signup-flow/BodySide.png'}
                />
            </div>

            <div className="submit">
                <ZFButton
                    size={"bigBoi"}
                    onClick={() => handleSave()}>
                    {t('client.activation.next')}
                </ZFButton>
            </div>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        picturesState: state.signup.photos,
        step: state.signup.step
    }
}

export default connect(mapStateToProps, { ...signup })(Pictures);


