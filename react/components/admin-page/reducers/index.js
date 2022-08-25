import { combineReducers } from 'redux';
import sales from './sales-reducer';
import clients from './client-reducer';


export default combineReducers({
    salesDashboard: sales,
    clientsScreen: clients,
    global: state => state || {}
});
