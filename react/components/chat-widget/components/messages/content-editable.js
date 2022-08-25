// https://github.com/ashleyw/react-sane-contenteditable/blob/master/src/react-sane-contenteditable.js
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import TextareaAutosize from 'react-textarea-autosize';
import { browser } from "../../utils";

const propTypes = {
    content: PropTypes.string,
    onChange: PropTypes.func,
    className: PropTypes.string,
    placeholder: PropTypes.string,
    name: PropTypes.string
};

const defaultProps = {
    name: 'message',
    className: '',
    placeholder: '',
    content: '',
    onChange: () => null
};

class ContentEditable extends Component {
    constructor(props) {
        super(props);
        this._element = React.createRef();
        this.state = {
            value: props.content,
        };
    }

    componentWillReceiveProps(nextProps) {
        if (nextProps.isOpen) {
            this.textarea.focus();
        }
        if (nextProps.content !== this.props.content) {
            this.setState({
                value: nextProps.content
            })
        }
    }

    _onChange = (e) => {
        let value = e.target.value;
        this.setValue(value);
    };

    _onPaste = (ev) => {
        const { maxLength } = this.props;

        ev.preventDefault();
        const text = ev.clipboardData.getData('text').substr(0, maxLength);
        if (browser.name === 'Firefox') {
            this.setValue(this.state.value + text);
        } else {
            document.execCommand('insertText', false, text);
        }
    };

    setValue(value, cb = () => null) {
        this.setState({ value: value }, () => {
            this.props.onChange(value.replace(/\n/g, "<br>"));
            cb();
        })
    };

    paste(content) {
        if (!this._element.current) {
            return console.warn('Unknown ref element');
        }
        const element = this._element.current;
        const { selectionStart, selectionEnd } = element;
        const { value } = this.state;
        //const sub1 = value.substring(0, selectionStart);
        //const sub2 = value.substring(selectionEnd, value.length);

        //const concatenated = [ sub1, content, sub2 ].join('');
        const val = value + ' ' + content;

        this.setValue(val, () => {
            this.textarea.focus();
            //element.selectionStart = sub1.length + content.length;
            //element.selectionEnd = sub1.length + content.length;
        })
    }

    replace(content) {
        this.setValue(content, () => {
            this.textarea.focus();
        });
    }

    flush() {
        this.setValue('');
    }

    render() {
        const { className, placeholder, name } = this.props;
        return (
            <TextareaAutosize
                ref={this._element}
                inputRef={tag => (this.textarea = tag)}
                className={className}
                placeholder={placeholder}
                name={name}
                value={this.state.value.replace(/<br>/g, '\n')}
                style={{ whiteSpace: 'pre-line', maxHeight: 180, ...this.props.style }}
                onChange={this._onChange}
                onPaste={this._onPaste}
                maxRows={8}
                useCacheForDOMMeasurements
            />
        );
    }
}

ContentEditable.propTypes = propTypes;
ContentEditable.defaultProps = defaultProps;

export default ContentEditable;
