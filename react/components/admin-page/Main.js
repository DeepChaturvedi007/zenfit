import React, {useEffect, useState} from 'react';
import SalesScreen from './screens/Sales'
import IngredientsScreen from './screens/Ingredients'
import ClientsScreen from './screens/Clients'
import {AuthContext} from "./authContext";

const Main = ({initial, screen, ...props}) => {
  const [authToken, setAuthToken] = useState(localStorage.getItem('token'));
  /*Get trainers on load*/

  useEffect(() => {
    if(initial){
      setToken(initial.authToken)
    }
  }, [initial]);

  const setToken = token => {
    localStorage.setItem('token', token);
    setAuthToken(token);
  };

  const screens = () =>{
    switch (screen) {
      case 'dashboard':
        return  <SalesScreen {...props} />;
      case 'ingredients':
        return  <IngredientsScreen {...props} />;
      case 'clients':
        return  <ClientsScreen {...props} />;
      default:
        return null;
    }
  }

  return(
      <AuthContext.Provider value={{ authToken}}>
        {screens()}
      </AuthContext.Provider>
      )
};

export default Main;
