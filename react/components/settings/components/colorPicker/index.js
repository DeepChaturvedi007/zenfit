import React, { useState, Fragment, useEffect } from 'react';
import { CustomPicker } from 'react-color'
import ExpandLessIcon from '@material-ui/icons/ExpandLess';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import { EditableInput, Saturation, Hue, Alpha } from 'react-color/lib/components/common';
import "./style.scss"

const Picker = () => {
    return (
      <div className="picker" />
    );
  }

const CustomInput = (props) => {
    const { type = "Hex", hex, rgb, setColor, hsl } = props;

    switch (type) {
      case 'Hex':
        return (
          <Fragment>
            <EditableInput style={{input: {width: "56px"}}} value={hex} onChange={data => setColor(data)}/>
            <EditableInput style={{input: {width: "56px"}}} value={`${rgb.a * 100}%`} onChange={data => setColor({...rgb, a: parseInt(data) / 100})}/>
          </Fragment>
        )
      case 'RGBA':
        return (
          <Fragment>
            <EditableInput value={rgb.r} onChange={data => setColor({...rgb, r: data})} />
            <EditableInput value={rgb.g} onChange={data => setColor({...rgb, g: data})} />
            <EditableInput value={rgb.b} onChange={data => setColor({...rgb, b: data})} />
            <EditableInput value={rgb.a} onChange={data => setColor({...rgb, a: data})} />
          </Fragment>
        )
      case 'HSLA':
        return (
            <Fragment>
                <EditableInput value={hsl.h} onChange={data => setColor({...hsl, h: data})} />
                <EditableInput value={hsl.s} onChange={data => setColor({...hsl, s: data})} />
                <EditableInput value={hsl.l} onChange={data => setColor({...hsl, l: data})} />
                <EditableInput value={hsl.a} onChange={data => setColor({...hsl, a: data})} />
          </Fragment>
        )
      default:
        return (
            <Fragment>
                <EditableInput style={{input: {width: "56px"}}} value={hex} onChange={data => setColor(data.Hex)}/>
                <EditableInput style={{input: {width: "56px"}}} value={`${rgb.a * 100}%`} onChange={data => setColor({...rgb, a: parseInt(data) / 100})}/>
          </Fragment>
        )
    }
}

const colorPicker = (props) => {
    const { setColor, hex, setPrimary } = props;
    const [ open, setOpen ] = useState(true);
    const [ type, setType ] = useState("Hex");
    useEffect(() => {
        setPrimary(hex);
      }, [hex]);
    return (
        <div id="color-picker">
            <div className="title box">
                <span className="title-circle" style={{backgroundColor: hex }} />
                { hex }
                <span className="title-blank"/>
                {
                    open ? <ExpandMoreIcon onClick={() => setOpen(false)}/> : <ExpandLessIcon onClick={() => setOpen(true)}/>
                }
            </div>
            <div className={`content box ${open ? "visible" : ""}`}>
                <div className="content-saturation">
                    <Saturation {...props} onChange={(e) => setColor(e)} pointer={Picker} />
                </div>
                <div className="content-hue">
                    <Hue {...props} onChange={(e) => setColor(e)} pointer={Picker} />
                </div>
                <div className="content-alpha">
                    <Alpha {...props} onChange={(e) => setColor(e)} pointer={Picker} />
                </div>
                <div className="flex">
                    <select name="type" className="content-select" onChange={ e => setType(e.target.value)}>
                        <option value="Hex">Hex</option>
                        <option value="RGBA">RGBA</option>
                        <option value="HSLA">HSLA</option>
                    </select>
                    <CustomInput {...props} type={type} />
                </div>
            </div>
        </div>
    )
  }
export default CustomPicker(colorPicker);

