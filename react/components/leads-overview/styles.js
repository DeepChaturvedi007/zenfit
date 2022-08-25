import { withStyles } from '@material-ui/core/styles';
import {
    blue
} from '@material-ui/core/colors'

const styles = withStyles((theme) => ({
    container: {
        padding: 10,
        '@media (max-width: 576px)': {
            padding: `10px 0px`
        }
    },
    searchField: {
        display: 'flex',
        marginTop: 40,
        marginBottom: 20
    },
    addNewButtonContent: {
        backgroundColor: blue['500'],
        '&:hover': {
            backgroundColor: blue['600'],
        }
    },
    addNewButtonText: {
        fontSize: 12,
        color: 'white'
    },
    addNewButtonVisible: {
        backgroundColor: blue['600']
    }
}))
export default styles;