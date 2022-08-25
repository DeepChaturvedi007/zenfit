import { withStyles } from '@material-ui/core/styles';

import {blue, grey, teal, pink} from '@material-ui/core/colors'

const styles = withStyles((theme) => ({
    filterContent: {
        display: 'flex',
        flexWrap: 'wrap'
    },
    filterItem: {
        padding: 10,
        border: '2px solid #d2d2d2',
        borderRadius: 10,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 10,
        marginBottom: 10,
        cursor: 'pointer',
        transition: 'all .3s',
        '&:hover': {
            boxShadow: '0px 0px 3px 3px #d2d2d2'
        }
    },
    filterCounter: {
        fontSize: 24,
        fontWeight: 'bold',
        color: 'black',
        margin: 0
    },
    filterName: {
        fontSize: 12,
        color: 'black',
        margin: 0,
        fontWeight: '100'
    },
    filterSelect: {
        borderColor: '#2895f1',
        '& p': {
            color: '#2895f1'
        }
    },
    searchContent: {
        padding: '2px 4px',
        display: 'flex',
        alignItems: 'center',
        border: '1px solid grey',
        minWidth: 300,
        '@media (max-width: 576px)': {
            minWidth: 0,
            marginRight: 10
        }
    },
    input: {
        marginLeft: theme.spacing(1),
        flex: 1,
        fontSize: 14
    },
    searchIcon: {
        padding: '0px 10px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        "& svg": {
            fontSize: 22,
            color: 'grey'
        }
    },
    leadTableContent: {
        width: '100%',
        borderCollapse: 'collapse',
        '& th, td': {
            padding: '15px 10px',
            border: '1px solid #dcdddd',
            '& .lead-name': {
                display: 'flex',
                alignItems: 'center'
            },
            '& .lead-name-text': {
                color: blue['600'],
                cursor: 'pointer',
                marginRight: 10
            },
            '& .lead-tag': {
                display: 'flex',
                flexWrap: 'wrap',
                '& span': {
                    padding: 3,
                    backgroundColor: 'black',
                    borderRadius: 2,
                    marginRight: 3,
                    marginBottom: 3,
                    lineHeight: '10px',
                    color: 'white'
                }
            },
            '& .utm-tag': {
                display: 'flex',
                flexWrap: 'wrap',
                '& span': {
                    padding: 3,
                    backgroundColor: '#2196f3',
                    borderRadius: 2,
                    marginRight: 3,
                    lineHeight: '10px',
                    color: 'white'
                }
            },
            '& .payment-resend': {
                color: blue['600'],
                cursor: 'pointer',
                marginLeft: 10
            },
            '& .won': {
                color: teal['A400']
            },
            '& .lost': {
                color: pink['A200']
            }
        },
        '& tbody tr': {
            transition: 'all .3s',
            '&:hover': {
                backgroundColor: grey['100']
            }
        }
    },
    dialogMessage: {
        fontSize: 12
    },
    emptyContent: {
        marginTop: 20,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        alignItems: 'center'
    },
    thContent: {
        '&::after': {
            content: '""',
            display: 'block',
            clear: 'both'
        }
    },
    thText: {
        float: 'left'
    },
    sortArray: {
        float: 'right',
        display: 'flex',
        flexDirection: 'column',
        '& .up': {
            border: '4px solid transparent',
            borderBottomColor: '#d6d3d3',
            width: 0,
            height: 0
        },
        '& .down': {
            marginTop: 4,
            border: '4px solid transparent',
            borderTopColor: '#d6d3d3',
            width: 0,
            height: 0
        },
        '& .up.active': {
            borderBottomColor: 'grey'
        },
        '& .down.active': {
            borderTopColor: 'grey'
        }
    }
}))
export default styles;
