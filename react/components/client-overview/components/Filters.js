import React from 'react';
import { connect } from "react-redux";

const Filters = (props) => {
    const { filters, filtersCount, currentFilter, onFilterChange, tagFilter, searchQuery } = props;
    let filterItems = [];

    React.useEffect(() => {
        let params = new URLSearchParams(location.search);
        if (params.get('filter')) {
            const filter = params.get('filter');
            onFilterChange(filter);
        }
    }, [])

    Object.keys(filters).forEach(key => {
        const filterCount = filtersCount[key] || 0;
        filterItems.push(
            <div
                className={`client-filters-item ${(key === currentFilter) ? 'active' : ''}`}
                data-filter-key={key}
                key={key}
                onClick={() => {
                    window.history.pushState({}, '', '/dashboard/clients?filter=' + key + '&q=' + searchQuery + '&tag=' + tagFilter);
                    onFilterChange(key);
                }}
            >

                {key === 'pending' && (
                    <span className='client-filters-item-badge'>
                        pending
                    </span>
                )}
                <div className="client-filters-item-content">
                    <div className="client-filters-item-count">{filterCount}</div>
                    <div className="client-filters-item-title">{filters[key]}</div>
                </div>
            </div>
        );
    });

    return (
        <div className="client-filters">
            {filterItems}
        </div>
    );
};

function mapStateToProps(state) {
    return {
        tagFilter: state.clients.tagFilter,
        searchQuery: state.clients.searchQuery,
    }
}

export default connect(mapStateToProps)(Filters);
