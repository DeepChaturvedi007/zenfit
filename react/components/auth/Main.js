import React, { Fragment } from 'react';
import { connect } from 'react-redux';
import LogIn from "./page/logIn"
import SignUp from "./page/signUp"
const Main = (props) => {
    const { view } = props

    return (
        <Fragment>
            {
                view == "signup"
                    ? <SignUp />
                    : <LogIn />
            }
        </Fragment>
    )
}

function mapStateToProps(state) {
    return {
        view: state.auth.view
    }
}

export default connect(mapStateToProps, { })(Main);
