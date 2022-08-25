
import React, {useEffect, useState} from "react";
import {TableCell, TableRow} from '@material-ui/core';
import Tooltip from '@material-ui/core/Tooltip';
import CheckIcon from '@material-ui/icons/Check';
import CloseIcon from '@material-ui/icons/Close';

const ClientItem = ({item}) =>{

    const [copyQuest, setCopyQuest] = useState(false)
    const [copyAct, setCopyAct] = useState(false)

    useEffect(() => {
        setTimeout(()=>{
            setCopyQuest(false);
            setCopyAct(false);
        },1500)
    }, [copyAct,copyQuest]);


    const formatTrueVals = (params) =>{
        if(params){
            return <CheckIcon/>
        }else {
            return <CloseIcon/>
        }
    }
    const copyToClipBoard = val =>{
        navigator.clipboard.writeText(val)
    }
    const toolTipTitle = val =>{
        return(<span className={"copyContainer "}>{val ? val : "No link was provided"} </span>)
    }

    return(
        <TableRow>
            <TableCell>
                <span>{item.name}</span>
            </TableCell>
            <TableCell>
                <span>{item.email}</span>
            </TableCell>
            <TableCell>
                {formatTrueVals(item.hasBeenActivated)}
            </TableCell>
            <TableCell>
                {formatTrueVals(item.accessApp)}
            </TableCell>
            <TableCell>
                {
                    item.hasBeenActivated ? (
                            <Tooltip
                                interactive
                                title={toolTipTitle(item.questionnaireUrl)}
                                placement={"left"}
                            >
                                <button
                                    className={ copyQuest ? "copied":""}
                                    onClick={() => { copyToClipBoard(item.questionnaireUrl); setCopyQuest(true) }}
                                >
                                    { copyQuest ? "Copied": "Copy Questionnaire link"}
                                </button>
                            </Tooltip>

                    ) : <span>Client needs to be active</span>
                }

            </TableCell>
            <TableCell>
                {
                    item.hasBeenActivated ? (
                        <Tooltip
                        interactive
                        title={toolTipTitle(item.activationUrl)}
                        placement={"left"}
                        >
                            <button
                                className={ copyAct ? "copied":""}
                                onClick={() => { copyToClipBoard(item.activationUrl); setCopyAct(true) }}
                            >
                                { copyAct ? "Copied": "Copy activation link"}
                            </button>
                        </Tooltip>

                    ) : <span>Client needs to be active</span>
                }


            </TableCell>
        </TableRow>
    )
}

export default ClientItem
