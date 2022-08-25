import React from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import { changeTagFilter } from "../store/clients/actions";
import { connect } from "react-redux";
import { prepareMultiOptions } from '../helpers';

const animatedComponents = makeAnimated();

const TagFilter = (props) => {
    const { changeTagFilter, tagFilter, searchQuery, tagsList, filterProperty } = props;

    const [tagValue, setTagValue] = React.useState([]);
    const tagSelect = (value) => {
        setTagValue(value);
        let searchValue = [];
        if (value !== null) {
            searchValue = value.reduce((accumulator, currentValue) => {
                accumulator.push(currentValue.value);
                return accumulator
            }, [])
        }
        if (tagFilter !== searchValue) {
            window.history.pushState({}, '', '/dashboard/clients?filter=' + filterProperty + '&q=' + searchQuery + '&tag=' + searchValue);
            changeTagFilter(searchValue);
        }
    }

    React.useEffect(() => {
        let params = new URLSearchParams(location.search);
        if (params.get('tag')) {
            const tags = params.get('tag').split(',');
            changeTagFilter(tags);
            const tagValues = prepareMultiOptions(tags);
            setTagValue(tagValues);
        }
    }, [])

    return (
        <div className='tag-filter hidden-xs hidden-sm'>
            <Select
                components={animatedComponents}
                isMulti
                options={prepareMultiOptions(tagsList)}
                value={tagValue}
                onChange={(value) => { tagSelect(value) }}
            />
        </div>
    )
}
function mapStateToProps(state) {
    return {
        tagFilter: state.clients.tagFilter,
        searchQuery: state.clients.searchQuery,
        tagsList: state.clients.tagsList,
        filterProperty: state.clients.filterProperty,
    }
}

export default connect(mapStateToProps, { changeTagFilter })(TagFilter);
