import React from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import axios from 'axios';
const animatedComponents = makeAnimated();

const TagFilter = (props) => {
    const {tags, handleChange, leadInfo} = props;
    const tagSelect = (value) => {
        let searchValue = [];
        if(value !== null){
            searchValue = value.reduce((accumulator, currentValue) => {
                accumulator.push(currentValue.label);
                return accumulator
            }, [])
        }
        handleChange(searchValue, 'tags')
    }

    const existingTags = leadInfo.tags.map(tag => {
        return {label: tag, value: tag};
    });

    return(
        <div className='tag-filter' hidden={tags.length === 0}>
            <div className="form-group">
                <label className="control-label">Assign to</label>
                <Select
                    defaultValue={existingTags}
                    components={animatedComponents}
                    isMulti
                    options={tags}
                    onChange={(value) => {tagSelect(value)}}
                />
            </div>
        </div>
    )
}

export default TagFilter;
