import React from 'react'

import Tags from '../Tags';
import {payment, week} from '../help-const';

const AddTags = (props) => {
    const {
        addTagsOpen,
        setAddTags,
        handleTagChange
    } = props;

    return(
        <div className="checkbox">
            <label style={{marginBottom: 10}}>
                <input
                    type="checkbox"
                    checked={addTagsOpen}
                    id="payment"
                    name="payment"
                    onChange={(e) => {setAddTags(!addTagsOpen)}}
                />
                Add client tags.
            </label>
            {addTagsOpen && (
                <Tags
                    handleChange={handleTagChange}
                />
            )}
        </div>
    )
}

export default AddTags;