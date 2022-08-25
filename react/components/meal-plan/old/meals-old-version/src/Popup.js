import React, { cloneElement, useState, useRef, useEffect, useImperativeHandle, forwardRef } from 'react';
import PropTypes from 'prop-types';
import includes from "lodash/includes";
import { PopupBox, PopupMenu, PopupOptions, PopupOption } from './components/Popup';
import { HorizontalDivider, Link } from './components/UI';

/**
 * @param {*} value
 * @param {*} itemValue
 * @param {boolean} isMultiple
 * @returns {boolean}
 */
const isSelected = (value, itemValue, isMultiple = false) => {
  if (isMultiple) {
    return value.length ? includes(value, itemValue) : itemValue === '';
  }
  return value === itemValue;
};

const Popup = forwardRef((props, ref) => {
  const node = useRef();
  const [isOpened, setOpened] = useState(false);

  const handleClickOutside = event => {
    if (!node.current.contains(event.target) && props.canClose) {
      setOpened(false);
    }
  };

  const handleTriggerClick = (event) => {
    event.preventDefault();

    if (isOpened && !props.canClose) {
      return;
    }

    setOpened(!isOpened);
  };

  const handleOptionSelect = (event, item, index) => {
    if (props.closeOnSelect && props.canClose) {
      setOpened(false);
    }
    props.onSelect(event, item, index);
  };

  useEffect(() => {
    if (isOpened) {
      document.addEventListener('mousedown', handleClickOutside);
    } else {
      document.removeEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpened]);

  useEffect(() => {
    if (isOpened) {
      props.onOpen && props.onOpen();
    } else {
      props.onClose && props.onClose();
    }
  }, [isOpened]);

  useImperativeHandle(ref, () => ({
    open: () => setOpened(true),
    close: () => setOpened(false),
  }));

  const trigger = cloneElement(props.renderTrigger(props), {
    onClick: handleTriggerClick,
  });

  let menuBody;

  if (props.renderBody) {
    menuBody = props.renderBody();
  } else {
    const options = props.options.map((item, index) => (
      <PopupOption selected={isSelected(props.value, item.value, props.multiple)} key={`option_${index}`}>
        {cloneElement(props.renderOption(item, index), {
          onClick: event => handleOptionSelect(event, item, index),
        })}
      </PopupOption>
    ));

    menuBody = (
      <PopupOptions>
        {options}
      </PopupOptions>
    );
  }

  return (
    <PopupBox ref={node} style={props.style}>
        {trigger}
        <PopupMenu opened={isOpened} position={props.position}>
          {menuBody}
        </PopupMenu>
    </PopupBox>
  );
});

Popup.propTypes = {
  canClose: PropTypes.bool,
  closeOnSelect: PropTypes.bool,
  value: PropTypes.any,
  options: PropTypes.array,
  onSelect: PropTypes.func,
  renderTrigger: PropTypes.func,
  renderBody: PropTypes.func,
  renderOption: PropTypes.func,
  onClose: PropTypes.func,
  onOpen: PropTypes.func,
  multiple: PropTypes.bool,
  position: PropTypes.oneOf(['left', 'right']),
};

Popup.defaultProps = {
  canClose: true,
  value: null,
  closeOnSelect: true,
  options: [],
  multiple: false,
  position: 'right',
  onSelect: () => {},
  renderTrigger: () => (
    <button type="button">Open</button>
  ),
  renderOption: (item) => (
    item.divider
      ? <HorizontalDivider/>
      : <Link href={item.href || '#'} modifier={item.modifier || 'secondary'}>{item.label}</Link>
  ),
};



export default Popup;
