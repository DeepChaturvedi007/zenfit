import React from 'react'

import Tooltip from '@material-ui/core/Tooltip';
import Typography from '@material-ui/core/Typography';
import Box from '@material-ui/core/Box';
// import Chip from '@material-ui/core/Chip';
import { makeStyles } from '@material-ui/core/styles';

const styles = makeStyles((theme) => ({
    popper: {
        zIndex: 100000
    },
    tooltip: {
        backgroundColor: 'white',
        boxShadow: '0px 5px 30px #c3c2c28a',
        color: 'black',
        fontSize: 12,
        padding: 0,
        // border: '1px solid grey',
        borderRadius: 10
    },
    arrow: {
        backgroundColor: 'transparent',
        color: 'white'
    },
    content: {

    },
    contentHeader: {
        padding: `${theme.spacing(2)}px ${theme.spacing(3)}px 0px`,
        fontSize: 14,
        // borderBottom: '1px solid grey',
        fontWeight: '900'
    },
    contentBody: {
        padding: `${theme.spacing(1)}px ${theme.spacing(3)}px`,
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'strech',
        '& ul': {
            listStyleType: 'none',
            margin: 0,
            padding: 0,
            flex: 1,
            '& li': {
                margin: '0px 0px 5px',
                fontWeight: '400',
                textTransform: 'lowercase',
                '&::first-letter': {
                    textTransform: 'uppercase'
                }
            }
        },
        '& img': {
            width: 50,
            height: 50,
            borderRadius: 50
        }
    },
    flexGrow: {
        borderRight: '1px solid #e6ebf1',
        margin: `0px ${theme.spacing(1)}px`
    },
    contentFooter: {
        display: 'flex',
        overflow: 'auto',
        padding: `${theme.spacing(1)}px ${theme.spacing(1)}px`,
        '& span': {
            marginRight: theme.spacing(1),
            padding: 5,
            backgroundColor: 'grey',
            borderRadius: 5,
            color: 'white'
        }
    }
}));
const RecipeTooltip = (props) => {
    const { children, recipe } = props;
    const classes = styles();
    return (
        <Tooltip
            title={
                <div>
                    <Typography className={classes.contentHeader}>{recipe.name}</Typography>
                    <Box className={classes.contentBody}>
                        <ul>
                            {recipe.ingredients.map((item, i) => {
                                return (
                                    i % 2 === 0 && (
                                        <li key={i}>{item.name}</li>
                                    )
                                )
                            })}
                        </ul>
                        <div className={classes.flexGrow} />
                        <ul>
                            {recipe.ingredients.map((item, i) => {
                                return (
                                    i % 2 === 1 && (
                                        <li key={i}>{item.name}</li>
                                    )
                                )
                            })}
                        </ul>
                        {/* <img src={recipe.image} alt="food" /> */}
                    </Box>
                    {/* <Box className={classes.contentFooter}>
                        {recipe.foodPreferences.map((item, i) => {
                            return (
                                <span key={i} >{item}</span>
                            )
                        })}
                    </Box> */}
                </div>
            }
            classes={{
                popper: classes.popper,
                tooltip: classes.tooltip,
                arrow: classes.arrow
            }}
            arrow
            placement="top-end"
            // open
        >
            {children}
        </Tooltip>
    )
}

export default RecipeTooltip;