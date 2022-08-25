/*jshint esversion: 6 */
import React, { useEffect, useState, Fragment } from 'react';
import moment from 'moment';
import Modal from '@material-ui/core/Modal';
import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";
import './styles.scss'
import axios from 'axios';
import { GET_WORKOUT_TEMPLATES } from '../../../../api/workout-api';
import ModalComponent from "../ClientDetails/Modules/ModalComponent";
import SectionInfoComponent from '../ClientDetails/Modules/SectionInfoComponent';
import {WORKOUT_LOCATIONS, LEVELS, GENDER, WORKOUTS_PER_WEEK, EXPERIENCE_LEVELS} from '../../const';
import IsThereDataComponent from "../ClientDetails/Modules/IsThereDataComponent";

export default function ModalWorkoutTemplates(props) {
    const {
        show,
        onClose,
        applyWorkoutTemplate,
        selectedClient
    } = props;

    const modalClose = React.useCallback((e) => {
        if(e.keyCode === 27){
            onClose()
        }
    }, [])

    const selectTemplate = (id, e) => {
        e.preventDefault();
        setLoading(true);
        applyWorkoutTemplate(id);
    };

    const updateFilters = (value, name) => {
        setFilters({ ...filters, [name]: value });
    };

    const [loading, setLoading] = useState(false);
    const [templates, setTemplates] = useState([]);
    const [filters, setFilters] = useState({
        location: null,
        gender: null,
        workoutsPerWeek: null,
        level: null
    });

    useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])

    /*useEffect(() => {
        if (show && selectedClient) {
            setFilters({
              gender: selectedClient.info.gender ? selectedClient.info.gender : null,
              location: selectedClient.info.workoutLocation ? selectedClient.info.workoutLocation : null,
              workoutsPerWeek: selectedClient.info.workoutsPerWeek ? selectedClient.info.workoutsPerWeek : null,
              level: selectedClient.info.experienceLevel ? selectedClient.info.experienceLevel : null
            });
        }
    }, [show, selectedClient]);*/

    useEffect(() => {
        const fetchData = async () => {
            const requestData = {
                workoutsPerWeek: filters.workoutsPerWeek !== 0 ? filters.workoutsPerWeek : null,
                location: filters.location !== 0 ? filters.location : null,
                gender: filters.gender !== 0 ? filters.gender : null,
                level: filters.level !== 0 ? filters.level : null
            };
            setLoading(true);
            const result = await axios.get(GET_WORKOUT_TEMPLATES(), { params: requestData })
            setTemplates(result.data);
            setLoading(false);
        }

        if (show) {
            fetchData();
        }
    }, [filters,show])

    return (
        <ModalComponent open={show} className={"workout-modal"} onClose={onClose} title={"Choose Workout Template"}>
            <div className="filter">
                <SectionInfoComponent
                    title={'Workouts Per Week'}
                    value={filters.workoutsPerWeek}
                    type={'btnGroup'}
                    name={'workoutsPerWeek'}
                    optionsList={WORKOUTS_PER_WEEK}
                    valueChange={updateFilters}
                />
                <SectionInfoComponent
                    title={'Experience Level'}
                    value={filters.level}
                    type={'btnGroup'}
                    name={'level'}
                    optionsList={LEVELS}
                    valueChange={updateFilters}
                />
                <SectionInfoComponent
                    title={'Gender'}
                    value={filters.gender}
                    type={'btnGroup'}
                    name={'gender'}
                    optionsList={GENDER}
                    valueChange={updateFilters}
                />

                <SectionInfoComponent
                    title={'Location'}
                    value={filters.location}
                    type={'btnGroup'}
                    name={'location'}
                    optionsList={WORKOUT_LOCATIONS}
                    valueChange={updateFilters}
                />

            </div>

            <div className="results">
                <IsThereDataComponent length={templates.length} name={"workout plans"} loading={loading} >
                    <Fragment>
                        {templates.map(template => {
                            return (
                                    <div key={template.id} className='workout-template-item'>
                                        <span>{template.name}</span>
                                        <span className={"videoTags"}>
                                            {template.meta.gender && <span>{GENDER[template.meta.gender]}</span>}
                                            {
                                                template.meta.workoutsPerWeek || template.meta.workoutsPerWeek !==0 && template.meta.workoutsPerWeek
                                                    ? <span> {WORKOUTS_PER_WEEK[template.meta.workoutsPerWeek]}</span>
                                                    : null
                                            }
                                            {template.meta.level && <span>{EXPERIENCE_LEVELS[template.meta.level]}</span>}
                                            {template.meta.location && <span>{WORKOUT_LOCATIONS[template.meta.location]}</span>}
                                        </span>
                                        <button className='btn btn-default' onClick={(e) => selectTemplate(template.id, e)}>
                                            Apply
                                        </button>
                                    </div>
                                )
                            })
                        }
                    </Fragment>
                </IsThereDataComponent>
            </div>

        </ModalComponent>

    );
}
