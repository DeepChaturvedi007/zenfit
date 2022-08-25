import React from 'react';
import CreatableSelect from 'react-select/creatable';
import makeAnimated from 'react-select/animated';
import { connect } from "react-redux";
import { prepareMultiOptions } from '../../helpers';

const animatedComponents = makeAnimated();
const TagFilter = (props) => {
    const { handleChange, tagsList } = props
    const tagSelect = (value) => {
        let searchValue = [];
        if (value !== null) {
            searchValue = value.reduce((accumulator, currentValue) => {
                accumulator.push(currentValue.label);
                return accumulator
            }, [])
        }
        handleChange(searchValue, 'tags')
    }

    return (
        <div className='tag-filter hidden-xs hidden-sm'>
            <CreatableSelect
                components={animatedComponents}
                isMulti
                options={prepareMultiOptions(tagsList)}
                onChange={(value) => { tagSelect(value) }}
            />
        </div>
    )
}

function mapStateToProps(state) {
    return {
        tagsList: state.clients.tagsList,
    }
}

export default connect(mapStateToProps)(TagFilter);
