import SearchTable from "../components/clients/table/searchTable"
import UniversalTable from "../components/clients/table/universalTable"
import {connect} from "react-redux";
import * as clientAdministrationAction from "../actions/client-actions";
import React from "react";


const Clients = ({clients}) => {
    return(
        <div className="clientAdministrationContainer">
            <h1>Client Administration</h1>
            <SearchTable/>
            {
                clients && (
                    <UniversalTable
                        items={clients}
                        tableTitles={["name","email","Active","Access to App","questionnaire url","activation url"]}
                        tableType="client"
                    />
                )
            }

        </div>
    )
}

function mapStateToProps(state){
    return{
        clients:state.clientsScreen.clients
    }
}

export default connect(mapStateToProps,{...clientAdministrationAction})(Clients);
