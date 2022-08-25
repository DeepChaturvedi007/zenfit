import React from 'react';
import BaseSlider from '@material-ui/core/Slider';
import { createMuiTheme, ThemeProvider } from '@material-ui/core/styles';

const theme = createMuiTheme({
  overrides: {
    MuiSlider: {
      root: {
        marginTop: '15px',
        marginBottom: '15px'
      },
      rail: {
        height: '4px',
        color: '#f1f1fe',
        opacity: 1
      },
      track: {
        height: '4px',
        color: '#1676ee',
        opacity: 1
      },
      thumb: {
        boxShadow: '0 7px 27px 0 rgba(43, 101, 249, 0.5)',
        color: '#1676ee',
        height: '20px',
        width: '20px',
        marginTop: '-8px',
        '&:focus, &:hover, &$active': {
          boxShadow: '0 7px 27px 0 rgba(43, 101, 249, 0.5)',
        },
        marginLeft: '-8px'
      },
      active: {
        boxShadow: '0 7px 27px 0 rgba(43, 101, 249, 0.5)',
      }
    }
  },
});

const Slider = (props) => {
  const {
    id = `${Math.random()}`,
    value,
    onChange,
    min = 0,
    max = 0,
    step = 1
  } = props;

  return (
    <ThemeProvider theme={theme}>
      <div style={{width: '100%'}}>
        <BaseSlider
          id={id}
          value={value || min}
          step={step}
          min={min}
          max={max}
          valueLabelDisplay={'off'}
          onChange={(e, value) => onChange(value)}
        />
      </div>
    </ThemeProvider>
  );
};

export default Slider;