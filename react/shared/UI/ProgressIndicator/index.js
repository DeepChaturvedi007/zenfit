import React from "react";
import ArrowBack from '@material-ui/icons/ArrowBack';
import {ZFProgressStyled} from "./ZFProgressStyled";
import PropTypes from 'prop-types'

const ZFProgressIndicator = ({ currentStep, bars, changeStep }) => {

    const handleBars = (step) => changeStep(step)

    const ProgressItems = () => {
        return(
            <div className="zf-progress">
                {
                    bars.map((el, i) => {
                        return(
                            <span
                                className={`span_${((currentStep >= el ? "active" :"inactive"))}`}
                                key={i}
                                style={{width:`${(100/bars.length)}%`}}
                                onClick={() => currentStep >= el && handleBars(el)}
                            >
                                <div
                                    style={{
                                        width:"100%",
                                        borderRadius:
                                            i === 0 ? "5px 0 0 5px" :
                                            i+1 === bars.length ? "0 5px 5px 0" : ""
                                    }}
                                    className={`zf-progressItem ${((currentStep >= el ? "active" :"inactive"))}`}
                                />
                            </span>
                        )
                    })
                }
            </div>
        )
    }

    return(
        <ZFProgressStyled className="removeHighLight">
            {
                currentStep > 1
                    ? <ArrowBack
                        className="backArrow"
                        onClick={() => currentStep !== 1 && handleBars(currentStep-1)}
                    />
                    : <div/>
            }
            <ProgressItems/>
            <span className="counts">
            {`${currentStep}/${bars.length}`}
            </span>
        </ZFProgressStyled>
    )
}

ZFProgressIndicator.propTypes = {
    currentStep: PropTypes.number.isRequired,
    bars: PropTypes.array.isRequired,
    changeStep: PropTypes.func.isRequired
}

export default ZFProgressIndicator
