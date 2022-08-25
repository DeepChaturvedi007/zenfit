import React, { Fragment, useState, useEffect } from 'react';
import { connect } from 'react-redux';
import * as auths from '../../store/auth/action';
import Checkbox from "@material-ui/core/Checkbox";
import ZFButton from "../../../../shared/UI/Button";
import ZFInputField from '../../../../shared/UI/InputField';
import ReportProblemOutlinedIcon from '@material-ui/icons/ReportProblemOutlined';
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';
import ReactPlayer from 'react-player';
import { Carousel } from 'react-bootstrap'
import { LOGO, LOGO_TITLE } from '../../const';
import "./styles.scss"
const Auth = (props) => {
    const { message, error, view, datakey } = props
    const { login, forgotPasswordClick, savePasswordClick, changeView } = props
    const [data, setData] = useState({
        "email": "",
        "password": "",
        "rememberMe": true,
        "resetEmail": "",
        "newPassword": "",
        "repeatPassword": "",
        "datakey": datakey
    });
    const handleData = (name, value) => {
        setData({ ...data, [name]: value })
    };
    const handleChange = (e) => {
        handleData(e.target.name, e.target.value);
    };
    const handleRememberMe = (e) => {
        handleData(e.target.name, e.target.checked);
    };

    useEffect(() => {
        const listener = e => {
            if (e.code === "Enter" || e.code === "NumpadEnter") {
                e.preventDefault();
                login(data);
            }
        };
        document.addEventListener("keydown", listener);
        return () => {
            document.removeEventListener("keydown", listener);
        };
    }, []);

    return (
        <Fragment>
            <div className="main">
                <div className="main-content">
                    <div className="main-logo">
                        <img src={LOGO} height="26" />
                        <img src={LOGO_TITLE} height="26" />
                    </div>
                    <div className={`main-change ${view == "login" ? "active" : ""}`}>
                        <div className="main-title">Log in</div>
                        <div className="main-second-title">Enter your details to log in as a trainer.</div>
                        <ZFInputField label="Email" name="email" onChange={handleChange}></ZFInputField>
                        <ZFInputField label="Password" name="password" type="password" onChange={handleChange}></ZFInputField>
                        <div className="main-remember">
                            <Checkbox id={'rem'} color="primary" name="rememberMe" onChange={handleRememberMe} checked={data.rememberMe} />
                            <label htmlFor={'rem'}>Remember me</label>
                            <span style={{ flexGrow: 1 }} />
                            <a className="blue-font" onClick={() => changeView("forgotPassword")}>Forgot password?</a>
                        </div>
                        {error != "" ?
                            <div className="main-error">
                                <ReportProblemOutlinedIcon />
                                {error}
                            </div> : ""
                        }
                        <ZFButton color="full" onClick={() => login(data)}>Sign in</ZFButton>
                        {/*
                        <div className="main-not-yet">
                            <span>Not registered yet?</span>
                            <a className="blue-font">Create an account</a>
                        </div>
                        */}
                    </div>
                    <div className={`main-change ${view == "forgotPassword" ? "active" : ""}`}>
                        <div className="main-title">Forgot password?</div>
                        <div className="main-second-title">Enter your email and we will send you a reset link</div>
                        <ZFInputField label="Email" name="resetEmail" onChange={handleChange} defaultValue={data.resetEmail}></ZFInputField>
                        {error != "" ?
                            <div className="main-error">
                                <ReportProblemOutlinedIcon />
                                {error}
                            </div> : ""
                        }

                        {message != "" ?
                            <div className="main-message">
                                <CheckCircleOutlineIcon />
                                {message}
                            </div> : ""
                        }
                        <ZFButton color="full" onClick={() => forgotPasswordClick(data)}>Reset password</ZFButton>
                        <div className="main-not-yet">
                            <a className="blue-font" onClick={() => changeView("login")}>Go back to login</a>
                        </div>
                    </div>
                    <div className={`main-change ${view == "newPassword" ? "active" : ""}`}>
                        <div className="main-title">New password</div>
                        <div className="main-second-title">Create a new password for your account</div>
                        <ZFInputField label="New password" name="newPassword" type="password" onChange={handleChange}></ZFInputField>
                        <ZFInputField label="Repeat password" name="repeatPassword" type="password" onChange={handleChange}></ZFInputField>
                        {error != "" ?
                            <div className="main-error">
                                <ReportProblemOutlinedIcon />
                                {error}
                            </div> : ""
                        }

                        {message != "" ?
                            <div className="main-message">
                                <CheckCircleOutlineIcon />
                                {message}
                            </div> : ""
                        }
                        <ZFButton color="full" onClick={() => savePasswordClick(data)}>Reset password</ZFButton>
                    </div>
                    <p className="main-footer">@2021 Zenfit All Rights Reserved</p>
                </div>
            </div>
            <div className="slider">
                <Carousel interval={null} >
                    <Carousel.Item>
                        <div className="slider-content">
                            <ReactPlayer url="https://www.youtube.com/watch?v=IAL4kGo1osM" controls width="100%" height="216px" />
                            <p className="slider-content-title">How to succeed as an online coach in 2021.</p>
                            <p>Zenfit founder Lasse Stokholm used to be an online coach himself - learn his simple, yet powerful tricks to become a successful online coach.</p>
                        </div>
                    </Carousel.Item>
                    <Carousel.Item>
                        <div className="slider-content">
                            <ReactPlayer url="https://www.youtube.com/watch?v=vJf4MNKx4n8" controls width="100%" height="216px" />
                            <p className="slider-content-title">Powerful tricks to get more clients through your SoMe channels.</p>
                            <p>Here are three easy ways to get more people to engage with your content - and ultimately get more clients.</p>
                        </div>
                    </Carousel.Item>
                    <Carousel.Item>
                        <div className="slider-content">
                            <ReactPlayer url="https://www.youtube.com/watch?v=9ooBgIhFt90" controls width="100%" height="216px" />
                            <p className="slider-content-title">How to structure your online clients</p>
                            <p>In this video I show how it's possible to handle clients in 5-10 minutes per week... with AWESOME service and BETTER CLIENT RESULTS than ever before.</p>
                        </div>
                    </Carousel.Item>
                </Carousel>
            </div>
        </Fragment>
    )
}

function mapStateToProps(state) {
    return {
        message: state.auth.message,
        view: state.auth.view,
        datakey: state.auth.datakey,
        error: state.auth.error
    }
}

export default connect(mapStateToProps, { ...auths })(Auth);