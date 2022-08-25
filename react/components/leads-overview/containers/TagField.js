import React from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import axios from 'axios';
const animatedComponents = makeAnimated();

const TagFilter = (props) => {
    const {handleChange, tags, placeholder} = props;
    const onSelect = value => {
      if (value) {
          handleChange(value.value);
      } else {
          handleChange('');
      }
    }

    return(
        <div className='tag-filter hidden-xs hidden-sm' style={{marginLeft: 10, width: 300}} hidden={tags.length === 0}>
            <Select
                isClearable
                options={tags}
                onChange={(value) => {onSelect(value)}}
                placeholder={placeholder}
            />
        </div>
    )
}

export default TagFilter;
