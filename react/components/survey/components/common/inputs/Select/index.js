import React from 'react';
import {
  InputLabel,
  MenuItem,
  FormControl,
  Select as BaseSelect,
  FormHelperText
} from '@material-ui/core';
import { createMuiTheme, ThemeProvider } from '@material-ui/core/styles';

const theme = createMuiTheme({
  overrides: {
    MuiFormControl: {
      root: {
        margin: '20px 0',
        width: "100%",
      }
    },
    MuiSelect: {
      root: {
        fontSize: '16px',
        display: 'flex',
        justifySelf: 'center',
        width: '100%',
        color: 'filled',
        backgroundColor: "transparent"
      },
      select: {
        '&:focus': {
          backgroundColor: 'transparent'
        },
      },
      filled: {
        marginTop: '10px',
        marginBottom: '10px',
        paddingBottom: '15px',
        paddingTop: '15px',
        paddingLeft: '10px',
      },
      icon: {
        fontSize: '24px',
        top: 'calc(50% - 16px)',
        color: "#d5d5d5"
      }
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
      },
      filled: {
        fontSize: '13px',
      },
      shrink: {
        transform: 'none'
      }
    },
    MuiFormHelperText: {
      root: {
        textTransform: "none",
        fontSize: "11px",
        fontWeight: 'normal',
        color: "#1c2023"
      }
    },
    MuiMenuItem: {
      root: {
        fontSize: '16px',
        lineHeight: 1.38,
        color: '#000',
        letterSpacing: '0.89px',
        padding: "7px 9px"
      }
    }
  }
});

const Select = (props) => {
  const {
    id = Math.random(),
    label = 'Select value',
    value = '',
    options = [],
    onChange,
    required,
    error,
    onFocus,
    onBlur
  } = props;

  const handleChange = (e) => {
    const option = options.find(option => option.value === e.target.value);
    onChange(option)
  };

  return (
    <ThemeProvider theme={theme}>
      <FormControl error={!!error} required={!!required}>
        <InputLabel id={`${id}-label`}>
          {label}
        </InputLabel>
        <BaseSelect
          labelId={`${id}-label`}
          id={`${id}`}
          value={value}
          onChange={handleChange}
          onClose={onBlur}
          onOpen={onFocus}
          MenuProps={{
            PaperProps: {
              style: {
                maxHeight: 300,
                width: 300,
                minWidth: 300,
                maxWidth: '100%'
              }
            }
          }}
        >
          {
            options.map((option, key) => (
              <MenuItem value={option.value} key={key}>
                {option.name}
              </MenuItem>
            ))
          }
        </BaseSelect>
        {!!error && <FormHelperText>{error}</FormHelperText>}
      </FormControl>
    </ThemeProvider>
  )
};

export default Select;