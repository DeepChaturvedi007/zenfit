export const FILTER_LIST = [
    {
        key:'all',
        value: 'All Leads',
        id: 0
    },
    {
        key: 'new',
        value: 'New',
        id: 1
    },
    {
        key: 'noAnswer',
        value: 'No Answer',
        id: 8
    },
    {
        key: 'inDialog',
        value: 'In Dialog',
        id: 2
    },
    {
        key: 'paymentWaiting',
        value: 'Offer Sent',
        id: 5
    },
    {
        key: 'won',
        value: 'Won',
        id: 3
    },
    {
        key: 'lost',
        value: 'Lost',
        id: 4
    },
];

export const TABLE_HEADER = [
    {
        key: 'name',
        value: 'Name',
        sortKey: 'name',
        sortable: false
    },
    {
        key: 'contactTime',
        value: 'Contact Time',
        sortKey: 'diffDays',
        sortable: true
    },
    {
        key: 'created',
        value: 'Created',
        sortKey: 'createDate',
        sortable: true
    },
    {
        key: 'phone',
        value: 'Phone',
        sortKey: 'phone',
        sortable: false
    },
    {
        key: 'status',
        value: 'Status',
        sortKey: 'status',
        sortable: false
    },
    {
        key: 'contact',
        value: 'Contact Again',
        sortKey: 'diffDays',
        sortable: true
    }
]

export const LEAD_STATUS = {
    '1': 'New',
    '2': 'In Dialog',
    '3': 'Won',
    '4': 'Lost',
    '5': 'Offer Sent',
    '8': 'No Answer'
}

export const CONTACT_TIME = {
    0: 'Whenever',
    1: '9-12',
    2: '12-15',
    3: '15-18',
    4: '18-21'
}

export const LOAD_MORE_LIMIT = 30
