import React, {useState} from 'react';
import TextField from '@material-ui/core/TextField';
import { createMuiTheme, ThemeProvider } from '@material-ui/core/styles';
import MuiPhoneNumber from 'material-ui-phone-number';
import _ from 'lodash';

const theme = createMuiTheme({
  overrides: {
    MuiTextField: {
      root: {
        fontSize: '16px',
        textTransform: 'uppercase',
        margin: '20px auto',
        display: 'flex',
        justifySelf: 'center',
        width: '100%',
      },
    },
    MuiInputLabel: {
      root: {
        fontSize: '13px',
        textTransform: 'uppercase',
        color: '#c1c7d4',
        '&$focused': {
          fontSize: '13px',
          textTransform: 'uppercase',
          color: '#c1c7d4',
          transform: 'none'
        },
        "&$error": {
          color: '#f44336'
        }
      }
    },
    MuiInput: {
      root: {
        color: '#2a3245',
        fontSize: '16px',
        paddingLeft: '10px',
        paddingRight: '10px'
      },
      underline: {
        '&:before': {
          borderBottomColor: '#c1c7d4'
        },
        '&:after': {
          borderBottomColor: '#c1c7d4'
        }
      },
    },
    MuiFormHelperText: {
      root: {
        textTransform: "none",
        fontSize: "11px",
        fontWeight: 'normal',
        color: "#1c2023"
      }
    }
  },
});

const Text = (props) => {
  const {
    id = `${Math.random()}`,
    label,
    value,
    type,
    onChange,
    description,
    error,
    ...otherProps
  } = props;

  switch (type) {
    case 'tel': {
      const locale = window.locale || 'en';
      const [lang, country] = locale.split('_');
      return (
        <ThemeProvider theme={theme}>
          <MuiPhoneNumber
            onChange={onChange}
            label={label}
            placeholder={undefined}
            defaultCountry={(country || 'us').toLowerCase()}
            localization={lang}
            InputLabelProps={{ shrink: true, placeholder: undefined }}
            disableAreaCodes
            error={!!error}
            helperText={error || description}
            {...otherProps}
          />
        </ThemeProvider>
      )
    }
    default: {
      return (
        <ThemeProvider theme={theme}>
          <TextField
            id={id}
            label={label}
            value={value}
            error={!!error}
            onChange={(e) => onChange(e.target.value)}
            helperText={error || description}
            {...otherProps}
          />
        </ThemeProvider>
      )
    }
  }
};

export default Text;