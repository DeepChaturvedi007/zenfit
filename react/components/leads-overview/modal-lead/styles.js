import { withStyles } from '@material-ui/core/styles';
import {grey, blue} from '@material-ui/core/colors'

const styles = withStyles((theme) => ({
    clientInfoContent: {
        marginTop: 20,
        '& .MuiGrid-item': {
            paddingTop: 0,
            paddingBottom: 5
        }
    },
    statusContent: {
        display: 'flex',
        flexWrap: 'wrap',
    },
    statusItem: {
        padding: '6px 16px',
        border: `1px solid ${grey['200']}`,
        backgroundColor: 'white',
        cursor: 'pointer',
        fontWeight: 'bold',
        fontSize: 14,
        '&.left': {
            borderRadius: '3px 0px 0px 3px'
        },
        '&.right': {
            borderRadius: '0px 3px 3px 0px'
        },
        '&.active': {
            borderColor: blue['600'],
            color: blue['600']
        }
    },
    disabled: {
        cursor: 'not-allowed',
        color: 'grey'
    },
    followUpContent: {
        '& .input-group-custom': {
            display: 'flex',
            width: '100%'
        },
        '& .input-group-addon': {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: '40px',
        },
        '& .form-control-custom': {
            width: '100%'
        }
    },
    footer: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        '& .delete': {
            color: 'red'
        },
        '& .disable': {
            cursor: 'not-allowed',
            opacity: 0.9,
            '&:focus': {
                outline: 'none'
            }
        }
    },
    tooltipContent: {
        maxWidth: 200,
        fontSize: 12,
        textAlign: 'center'
    },
    popperContent: {
        zIndex: 1000000
    },
    modalBody: {
        '@media (min-width: 600px)' : {
            display: 'flex',
            justifyContent: 'space-between',
            '& .separator': {
                borderRight: '1px solid #d4d4d4',
                maxWidth: '10%'
            },
            '& .leadInfo': {
                maxWidth: '45%'
            },
            '& .salesInfo': {
                maxWidth: '45%'
            }
        }
    }

}))
export default styles;
