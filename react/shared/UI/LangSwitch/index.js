import React, {useEffect, useRef, useState} from "react";
import {LANGUAGES} from "./consts";
import KeyboardArrowDownIcon from '@material-ui/icons/KeyboardArrowDown';
import KeyboardArrowUpIcon from '@material-ui/icons/KeyboardArrowUp';
import {ZFLangSwitchStyled} from "./ZFLangSwitchStyled";

const ZFLangSwitch = ({ changeLang, choosenLang }) => {
    const [open, setOpen] = useState(false);
    const wrapperRef = useRef("");

    const handleChoosenLang = (newLang) =>{
        changeLang(newLang)
        setOpen(!open)
    }

    useEffect(() => {
        if (open) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [open]);

    const handleClickOutside = (event) => {
        if (wrapperRef && !wrapperRef.current.contains(event.target) && open) {
            setOpen(!open)
        }
    }

    const LangList = () => {
        return(
            <div className="langList">
                {
                    Object.values(LANGUAGES).map((lang, index) => {
                        return(
                            <div
                                key={index}
                                onClick={() => handleChoosenLang(lang.val)}
                                className={`langItem ${(choosenLang === lang.val ? "active" :"")}`}
                            >
                                <span className={"flag"}>{lang.flag}</span>
                                <span>{lang.name}</span>
                            </div>
                        )
                    })
                }
            </div>
        )
    }

    return(
        <ZFLangSwitchStyled className={`removeHighLight `} ref={wrapperRef}>
            <div className={`langBtn ${open && "active"}`} onClick={() => setOpen(!open)}>
                <span className={"flag"}>
                    {LANGUAGES[choosenLang].flag}
                </span>
                <span className={`flagName ${open && 'active'}`}>{LANGUAGES[choosenLang].name}</span>
                {!open
                    ? <KeyboardArrowDownIcon/>
                    : <KeyboardArrowUpIcon/>
                }
            </div>
            {
                open && <LangList/>
            }
        </ZFLangSwitchStyled>
    )
}
export default ZFLangSwitch
