import React from 'react';
import PropTypes from 'prop-types';
import { makeStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import Tabs from '@material-ui/core/Tabs';
import Tab from '@material-ui/core/Tab';
import Box from '@material-ui/core/Box';
import Radio from "../../../survey/components/common/inputs/Deprecated/Radio";

function TabPanel(props) {
    const { children, value, index, ...other } = props;

    return (
        <div
            role="tabpanel"
            hidden={value !== index}
            id={`tabpanel`}
            aria-labelledby={`simple-tab-${index}`}
            {...other}
        >
            {value === index && (
                <Box p={3}>
                    {children}
                </Box>
            )}
        </div>
    );
}

TabPanel.propTypes = {
    children: PropTypes.node,
    index: PropTypes.any.isRequired,
    value: PropTypes.any.isRequired,
};

const useStyles = makeStyles((theme) => ({
    root: {
        flexGrow: 1,
        backgroundColor: theme.palette.background.paper,
    },
}));

export default function MacroTabs(props) {
    const classes = useStyles();
    const {handleChangeKcal} = props;
    const [value, setValue] = React.useState(0);

    const handleChange = (event, newValue) => {
        if(value !== newValue){
            handleChangeKcal(newValue+1)
        }
        setValue(newValue);
    };

    return (
        <div className={"macroTabsContainer"}>
            <AppBar position="static">
                <Tabs value={value} onChange={handleChange} aria-label="simple tabs example"  variant="fullWidth">
                    <Tab label="Fixed macro split " icon={<Radio checked={value===0}/>}/>
                    <Tab label="Custom macro split" icon={<Radio checked={value===1}/>}/>
                </Tabs>
            </AppBar>
            <TabPanel value={value} index={0}>
                {props.children[0]}
            </TabPanel>
            <TabPanel value={value} index={1}>
                {props.children[1]}
            </TabPanel>
        </div>
    );
}
