import React from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';

const animatedComponents = makeAnimated();
const Tags = (props) => {
    const { clientDetail, clientDurationUpdate, tagsList } = props;

    const tagsValue = clientDetail.tags.map(item => ({ label: item.title, value: item.id }));

    const tagSelect = (value) => {
        let selectedValue = [];
        if (value !== null) {
            selectedValue = value.map((item) => item.label)
        }
        if (value !== selectedValue) {
            clientDurationUpdate(selectedValue, clientDetail.id, 'tags');
        }
    }

    return (
        <div className='select-tags'>
            <Select
                components={animatedComponents}
                isMulti
                defaultValue={tagsValue}
                options={tagsList}
                onChange={(value) => { tagSelect(value) }}
            />
        </div>
    )
}

export default Tags;