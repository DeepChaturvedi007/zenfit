import React from 'react';
import BaseCheckbox from '@material-ui/core/Checkbox';
import {ThemeProvider, createMuiTheme} from "@material-ui/core";
const theme = createMuiTheme({
  overrides: {
    MuiCheckbox: {
      root: {
        padding: 0,
        marginLeft: '7px',
        marginRight: '7px',
        color: '#1676ee',
        fontSize: '20px',
        backgroundColor: 'transparent',
        borderRadius: '6px',
        "&$checked": {
          color: '#1676ee',
        }
      },
      colorPrimary: {
        color: '#1676ee',
      }
    },
    MuiSvgIcon: {
      root: {
        fontSize: '20px',
        color: '#1676ee',
      }
    }
  },
});

const Checkbox = ({ checked, onChange }) => {
  const handleChange = event => {
    onChange(event.target.checked);
  };

  return (
    <ThemeProvider theme={theme}>
      <BaseCheckbox
        checked={checked}
        color="primary"
        onChange={handleChange}
        inputProps={{ 'aria-label': 'secondary checkbox' }}
      />
    </ThemeProvider>
  );
};

export default Checkbox;