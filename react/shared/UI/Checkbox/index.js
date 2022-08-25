import React, {Fragment} from "react";
import Checkbox from "@material-ui/core/Checkbox";
import {ZFCheckboxStyled} from "./ZFCheckboxStyled";
import { UitCheckSquare } from '@iconscout/react-unicons-thinline'
import { UitSquareFull } from '@iconscout/react-unicons-thinline'
import {ACTIVE_COLOR} from "../Theme/_color";

const ZFCheckbox = ({title, subtitle, disabled, name, onChange, component, checked, size}) => {
    return (
        <ZFCheckboxStyled size={size}>
            <Checkbox
                disabled={disabled}
                icon={
                    checked
                        ? <UitCheckSquare size={'17px'} color={ACTIVE_COLOR}/>
                        : <UitSquareFull size={'17px'}/>
                }
                checkedIcon={<UitCheckSquare size={'17px'} color={ACTIVE_COLOR}/>}
                id={'checkbox'}
                onChange={onChange}
                inputProps={{ 'aria-label': 'controlled' }}
                name={name}
                color="primary"
                checked={checked}
            />
            <label htmlFor={'checkbox'}>
                {
                    component ? (
                        component()
                    ) : (
                        <Fragment>
                            <div className="title">{title}</div>
                            <div className={"subtitle"}>{subtitle}</div>
                        </Fragment>

                    )
                }
            </label>
        </ZFCheckboxStyled>
    )
}

export default ZFCheckbox;
