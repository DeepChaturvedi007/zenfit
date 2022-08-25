import React, { Fragment, useState } from 'react';
import { connect } from 'react-redux';
import * as settings from './store/settings/action';
import ZFTabBar from "../../shared/UI/TabBar";
import SaveButton from "./components/saveButton"
import ColorPicker from "./components/colorPicker"
import ZFInfo from "../../shared/UI/Info";
import ZFButton from "../../shared/UI/Button";
import ZFInputField from '../../shared/UI/InputField';
import ZFBreadcrum from '../../shared/UI/Breadcrum';
import ZFCheckbox from "../../shared/UI/Checkbox";
import TextField from '@material-ui/core/TextField'
import ReactPlayer from 'react-player';
import InvoiceTable from './components/InvoiceTable';
import CreditCardIcon from '@material-ui/icons/CreditCard';
import AccountBalanceIcon from '@material-ui/icons/AccountBalance';
import InputAdornment from '@material-ui/core/InputAdornment';
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';
import { CARD_CONNECTED, CARD_EMPTY, TABS, VIDEO_EMPTY, STRIPE_CONNECT_URL, IPHONE_COLOR_PICKER } from './const';

const Main = (props) => {
    const {
        settings,
        stripeConnect,
        saveStatus
    } = props;
    const { saveGeneralSetting, editStripeSetting, uploadPhoto, uploadLogo, changeSaveStatus, changePassword } = props;
    const [activeTab, setActiveTab] = useState(1);
    const [upload, setUpload] = useState(settings.companyLogo);
    const [photo, setPhoto] = useState(settings.profilePicture);
    const [password, setPassword] = useState({
        password: "",
        repeatPassword: "",
        currentPassword: ""
    })
    const [data, setData] = useState({
        firstName: settings.firstName || "",
        lastName: settings.lastName || "",
        companyName: settings.companyName || "",
        email: settings.email || "",
        phone: settings.phone || "",
        video: settings.video || "",
        welcomeMessage: settings.welcomeMessage || "",
        vat: settings.vat || "",
        receiveEmailOnNewLead: settings.receiveEmailOnNewLead || false,
        receiveEmailOnNewMessage: settings.receiveEmailOnNewMessage || false,
        primaryColor: settings.primaryColor || "",
    });
    const [ color, setColor ] = useState(data.primaryColor);
    const [validator, setValidator] = useState({
        firstName: "",
        lastName: "",
        companyName: "",
        email: "",
        repeatPassword: "",
        changeFlag: false,
        passwordMatchFlag: true,
    });
    const handlePassword = (name, value) => {
        let pass = password;
        pass[name] = value
        setPassword({ ...pass });
        if (pass.password != pass.repeatPassword) {
            setValidator({ ...validator, repeatPassword: "Passwords doesn’t match", passwordMatchFlag: false })
        } else {
            setValidator({ ...validator, repeatPassword: "", passwordMatchFlag: true })
        }
    };
    const handleData = (name, value) => {
        if(saveStatus != "Save") changeSaveStatus("Save")
        let flag = false, kvalue = data;
        kvalue = { ...kvalue, [name]: value }
        Object.keys(kvalue).forEach(key => {
            if (kvalue[key] != settings[key] && !(kvalue[key] == "" || kvalue[key] == "#000000" && !settings[key]))  {
                flag = true;
            }
        });
        setValidator({ ...validator, changeFlag: flag })
        setData({ ...kvalue })
    };
    const handleChange = (e) => {
        handleData(e.target.name, e.target.value);
    };
    const handleCheckbox = (e) => {
        handleData(e.target.name, e.target.checked);
    };
    const handleColor = (e) => {
        handleData("primaryColor", e);
    };
    const currentTab = TABS.find(item => item.value == activeTab)
    const onClickSave = () => {
        let flag = true, error = validator, firstKey = "";
        Object.keys(validator).forEach(key => {
            if (data[key] == ""  ) {
                if (flag) firstKey = key
                error[key] = "please input"
                flag = false;
            } else if (key != "changeFlag" && key != "passwordMatchFlag")  {
                error[key] = ""
            }
        });
        setValidator({ ...error });
        if (flag) {
            saveGeneralSetting(data);
        } else {
            document.querySelector(`input[name=${firstKey}]`).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" })
        }
    };
    const handleUpload = (file) => {
        setPhoto(file);
        uploadPhoto(file);
    };
    const handleLogo = (file) => {
        setUpload(file);
        uploadLogo(file);
    };
    const onClickChangePassword = () => {
        if(password.password.length < 6) {
            toastr.error("Your password must be at least 6 characters long.");
        } else {
            changePassword({
                password: password.currentPassword,
                password1: password.password,
                password2: password.repeatPassword
            })
        }
    }

    return (
        <Fragment>
            <div style={{ position: 'relative' }}>
                <ZFTabBar tabs={TABS} activeTab={activeTab} onNavigateTab={setActiveTab} />
                <ZFBreadcrum url={`Settings/${currentTab.label}`} className="moveNav" />
            </div>
            {activeTab === 1 && (
                <Fragment>
                    <ZFInfo
                        title="General Information"
                        content="Enter your personal information which we will use to contact you.">
                        <div className="personal">
                            <ZFInputField type="file" label="Profile picture" onChange={handleUpload} value={photo} />
                        </div>
                        <ZFInputField label="First name" name="firstName" onChange={handleChange} defaultValue={data.firstName} helperText={validator.firstName} />
                        <ZFInputField label="Last name" name="lastName" onChange={handleChange} defaultValue={data.lastName} helperText={validator.lastName} />
                        <ZFInputField label="E-Mail" name="email" onChange={handleChange} defaultValue={data.email} helperText={validator.email} />
                        <ZFInputField label="Phone" name="phone" onChange={handleChange} defaultValue={data.phone} />
                    </ZFInfo>
                    <ZFInfo
                        title="Company Information"
                        content="Your company name should be your registered company.">
                        <ZFInputField type="file" label="Company logo" onChange={handleLogo} value={upload} />
                        <ZFInputField label="Company name" name="companyName" onChange={handleChange} defaultValue={data.companyName} helperText={validator.companyName} />
                        <ZFInputField label="VAT / EIN number" name="vat" onChange={handleChange} defaultValue={data.vat} />
                    </ZFInfo>
                    <ZFInfo
                        title="Change password"
                        content="Please make sure you use a strong and unique password.">
                        <ZFInputField label="Current password" type="password" onChange={(e) => handlePassword("currentPassword", e.target.value)}/>
                        <ZFInputField label="New password" type="password" name="password" onChange={(e) => handlePassword("password", e.target.value)} />
                        <ZFInputField label="Repeat password" type="password" onChange={(e) => handlePassword("repeatPassword", e.target.value)} helperText={validator.repeatPassword} />
                        <span className={`change-password ${(validator.passwordMatchFlag && password.repeatPassword !="") ? "visible" : ""}`}>
                            <ZFButton color={`${(validator.passwordMatchFlag && password.repeatPassword !="" && password.currentPassword) ? "primary" : ""}`} disabled={ (validator.passwordMatchFlag && password.repeatPassword !="" && password.currentPassword !="" ) ? false : true } onClick={onClickChangePassword}>Change password</ZFButton>
                        </span>
                    </ZFInfo>
                    <ZFInfo
                        title="Automatically charge your clients"
                        content="Our payment system is powered by Stripe.com. This means that you have to create an account with Stripe in order to receive payments from your clients.<br/><br/>
                        Zenfit Processing Fee: 2,4%<br/>
                        (Stripe fees not included)">
                        <div className="stripe-detail">
                            <div className="stripe-detail--title">
                                <p>Invoice from {data.companyName}</p>
                            </div>
                            <div className="stripe-detail--content">
                                <div className="stripe-detail--content--input">
                                    <TextField disabled variant="outlined" InputProps={{ startAdornment: <InputAdornment position="start"><CreditCardIcon />&nbsp;Card</InputAdornment> }} style={{ marginRight: '10px' }} />
                                    <TextField disabled variant="outlined" InputProps={{ startAdornment: <InputAdornment position="start"><AccountBalanceIcon />&nbsp;Bank</InputAdornment> }} />
                                </div>
                                <TextField disabled variant="outlined" InputProps={{ startAdornment: <InputAdornment position="start"><CreditCardIcon />&nbsp;Card Number</InputAdornment>, endAdornment: <InputAdornment position="end">MM / YY&nbsp; CVC&nbsp;</InputAdornment> }} />
                                <ZFButton color="grey full" disabled>Pay invoice</ZFButton>
                                <div className="button-center">
                                    {
                                        stripeConnect ? (
                                            <React.Fragment>
                                                <ZFButton disabled><CheckCircleOutlineIcon />Connected</ZFButton>
                                                <div className="checkmark">
                                                    <CheckCircleOutlineIcon/><br/>
                                                    CONNECTED
                                                </div>
                                            </React.Fragment>
                                        ) : <a href={STRIPE_CONNECT_URL}><ZFButton color="primary">Connect with <b>Stripe</b></ZFButton></a>
                                    }
                                </div>
                            </div>
                        </div>
                    </ZFInfo>
                    <ZFInfo
                        title="App Personalization"
                        content="Give a personalized experience to your clients when they use the Zenfit App.<br/><br/>
                        Record and upload a personal welcome message to your clients. It will appear when they use the Zenfit app. The welcome message will be shown with your name, profile picture and company info when your clients use the app.">
                        <div className="player">
                            <ReactPlayer url={data.video} controls width="100%" height="225px" className={`video ${(data.video && data.video != "") ? "visible" : ""}`} />
                            <div className={`video-placeholder ${(data.video && data.video != "") ? "" : "visible"}`}>
                                <img src={VIDEO_EMPTY} width="150" />
                            </div>
                        </div>
                        <ZFInputField label="YouTube or Vimeo link" name="video" onChange={handleChange} defaultValue={data.video} />
                        <ZFInputField label="Introduction message" name="welcomeMessage" onChange={handleChange} multiline defaultValue={data.welcomeMessage} />
                        <div className="colorPickerSection">
                            <ColorPicker color={color} setColor={setColor} setPrimary={handleColor} />
                            <div className="iphone-color" style={{backgroundColor: data.primaryColor}}>
                                <img src={IPHONE_COLOR_PICKER} width="220" />
                            </div>
                        </div>
                    </ZFInfo>
                    <ZFInfo
                        title="Email notifications"
                        content="Update which notifications you want to receive to your email adress.">
                        <ZFCheckbox
                            title="New Messages"
                            subtitle="Receive an email every time a client writes a message"
                            onChange={handleCheckbox}
                            name="receiveEmailOnNewMessage"
                            checked={data.receiveEmailOnNewMessage}
                        />
                        <ZFCheckbox
                            title="New Leads"
                            subtitle="Receive an email every time you get a new lead"
                            onChange={handleCheckbox}
                            name="receiveEmailOnNewLead"
                            checked={data.receiveEmailOnNewLead}
                        />
                    </ZFInfo>
                    <ZFInfo
                        title="Card information"
                        content="Setup your card details, in order to pay for your Zenfit services.">
                        <div className="card-information">
                            {(settings.defaultCard && Object.keys(settings.defaultCard).length) ? (
                                <React.Fragment>
                                    <img src={CARD_CONNECTED} width="400" style={{ boxShadow: "40px 40px 40px #a7c8fe" }} />
                                    <span className="card-information--digit">{settings.defaultCard.last4}</span>
                                </React.Fragment>
                            ) : (
                                <img src={CARD_EMPTY} width="400" style={{ boxShadow: "40px 40px 40px #b3b3b3" }} />
                            )}
                        </div>
                        <div className="button-pair">
                            <ZFButton onClick={editStripeSetting}>Edit card details</ZFButton>
                        </div>
                    </ZFInfo>
                    <ZFInfo
                        title="Invoices"
                        content="Here you can find all your invoices from Zenfit.">
                        <InvoiceTable data={settings.invoices ? settings.invoices : []} />
                    </ZFInfo>
                    <span className={`setting-save ${ validator.changeFlag ? "visible" : "" }`}>
                        <SaveButton status={saveStatus} onClickSave={onClickSave} />
                    </span>
                </Fragment>
            )}
        </Fragment>
    )
}

function mapStateToProps(state) {
    return {
        settings: state.settings.settings,
        stripeConnect: state.settings.stripeConnect,
        saveStatus: state.settings.saveStatus
    }
}

export default connect(mapStateToProps, { ...settings })(Main);
