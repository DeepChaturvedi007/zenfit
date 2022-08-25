import React from 'react';
import * as signup from '../../store/signup/action';
import {connect} from 'react-redux';

const Done = ({userApp}) => {

    const iPhoneLink = userApp ? userApp.iphone :'https://apps.apple.com/dk/app/zenfit-for-clients/id1155897823'
    const iPhoneImg = '/bundles/app/images/client-signup-flow/AppleAppStore.png'

    const googleLink = userApp ? userApp.android : 'https://play.google.com/store/apps/details?id=com.zenfit_app'
    const googleImg = '/bundles/app/images/client-signup-flow/GoogleStore.png'

    return (
        <div className={"zf-Done"}>
            <a className="iphone" target={'_blank'} href={iPhoneLink}>
                <img src={iPhoneImg} alt="DownLoadAppToIphone"/>
            </a>
            <a className="Google" href={googleLink} target={'_blank'}>
                <img src={googleImg} alt="DownLoadAppToAndroid"/>
            </a>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        userApp: state.signup.userApp
    }
}

export default connect(mapStateToProps, { ...signup })(Done);
