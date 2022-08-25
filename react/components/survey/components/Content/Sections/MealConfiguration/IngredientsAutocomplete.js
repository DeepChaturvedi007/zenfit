import React, {useState} from 'react';
import Text from "../../../common/inputs/Text";
import Chip from "@material-ui/core/Chip";
import { ThemeProvider, createMuiTheme } from '@material-ui/core/styles';
import Autocomplete, {createFilterOptions} from "@material-ui/lab/Autocomplete/Autocomplete";
import { suggest } from '../../../../api/recipes';
import ClearIcon from '@material-ui/icons/Clear';
import CircularProgress from "@material-ui/core/CircularProgress";
import {useTranslation} from "react-i18next";

const filter = createFilterOptions();

const theme = createMuiTheme({
  overrides: {
    MuiAutocomplete: {
      root: {
        width: '100%'
      },
      tag: {
        backgroundColor: '#2a3245',
        borderRadius: '15px',
        color: "#ffffff",
        textTransform: 'none',
        fontWeight: 'bold',
        fontSize: '14px',
        padding: '7px 10px',
        direction: 'rtl',
      },
      option: {
        fontSize: '16px',
        lineHeight: 1.38,
        color: '#000',
        letterSpacing: '0.89px',
        padding: "7px 9px"
      },
      input: {
        paddingLeft: '10px !important'
      },
      popper: {
      }
    },
    MuiChip: {
      deleteIcon: {
        color: "#b9c0cf",
        height: '15px',
        width: '15px',
        '&:hover, &:active, &:focus': {
          color: "#b9c0cf",
        },
        margin: '0 5px'
      },
      label: {
        paddingRight: '7px',
        paddingLeft: 0
      }
    }
  }
});

const Item = ({option, ...props}) => {
  return (
    <ThemeProvider theme={theme}>
      <Chip
        variant="outlined"
        deleteIcon={<ClearIcon />}
        label={option.name}
        {...props}
      />
    </ThemeProvider>
  )
};

const IngredientsAutocomplete = (props) => {
  const {t} = useTranslation('main');
  const [options, setOptions] = useState([]);
  const [loading, setLoading] = useState(false);
  const {
    values,
    onChange: setValues,
  } = props;

  const handleChange = (e, values) => {
    const all = values.find(value => !value.id);
    if(all) {
      const toSelect = options.filter(value => !!value.id);
      setValues(toSelect);
    } else {
      setValues(values);
    }
  };

  const handleSearch = (value) => {
    setLoading(true);
    suggest(value)
      .then(options => {
        setOptions(options);
        setLoading(false);
      });
  };

  const filterOptions = (options, params) => {
    const filtered = filter(options, params);
    const { inputValue: name } = params;
    if (!!name && !options.length) {
      filtered.push({
        id: Math.random(),
        name,
      });
    }
    if(options.length > 1) {
      filtered.unshift({name: t('meal.exclusion.input.selectAll')});
    }
    return filtered;
  };

  const renderInput = params => (
    <Text
      label={t('meal.exclusion.input.label')}
      placeholder={t('meal.exclusion.input.placeholder')}
      onChange={(value) => handleSearch(value)} {...params}
      InputProps={{
        ...params.InputProps,
        endAdornment: (
          <React.Fragment>
            {loading ? <CircularProgress color="inherit" size={20} /> : null}
            {params.InputProps.endAdornment}
          </React.Fragment>
        ),
      }}
    />
  );

  const renderTags = (value, getTagProps) => (
    value.map((option, index) =>
      <Item option={option} {...getTagProps({ index })} />
    )
  );

  const getOptionLabel = option => option.label || option.name;

  return (
    <ThemeProvider theme={theme}>
      <Autocomplete
        multiple
        disableClearable
        options={options}
        value={values}
        loading={loading}
        getOptionLabel={getOptionLabel}
        onChange={handleChange}
        filterOptions={filterOptions}
        renderInput={renderInput}
        renderTags={renderTags}
      />
    </ThemeProvider>
  )
};

export default IngredientsAutocomplete;