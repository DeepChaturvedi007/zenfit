import React from 'react';
import { connect } from "react-redux";
import { changeSearchQuery } from "../store/clients/actions";
import { debounce } from 'lodash';

class SearchField extends React.Component {
    searchDebounce = null;

    constructor(props) {
        super(props);

        this.state = {
            q: ''
        };

        this.changeQuery = debounce(this.changeQuery.bind(this), 500, { leading: false, maxWait: 1000, trailing: true });
    }

    componentDidMount() {
        let params = new URLSearchParams(location.search);
        if (params.get('q')) {
            this.setState({ q: params.get('q') });
        }
    }

    handleInputChange(value) {
        this.setState({ q: value });
        this.changeQuery(value);
    }
    
    changeQuery(value) {
        const { changeSearchQuery, searchQuery, tagFilter, filterProperty } = this.props;

        if (searchQuery !== value) {
            window.history.pushState({}, '', '/dashboard/clients?filter=' + filterProperty + '&q=' + value + '&tag=' + tagFilter);
            if (value.length > 1 || value.length === 0) {
                clearTimeout(this.searchDebounce)
                this.searchDebounce = setTimeout(() => changeSearchQuery(value), 500);
            }
        }
    }

    render() {
        return (
            <div className="search-field">
                <span className="search-field-icon"><i className="material-icons">search</i></span>
                <input
                    className="search-field-input"
                    type="text"
                    placeholder="Search by name, email or label"
                    value={this.state.q}
                    onChange={({ target: { value } }) => this.handleInputChange(value)}
                />
            </div>
        );
    }
}

function mapStateToProps(state) {
    return {
        searchQuery: state.clients.searchQuery,
        tagFilter: state.clients.tagFilter, 
        filterProperty: state.clients.filterProperty,
    }
}

export default connect(mapStateToProps, { changeSearchQuery })(SearchField);
