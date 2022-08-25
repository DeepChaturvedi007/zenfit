import React, {Component} from 'react';
import PlusIcon from "../../../chat-widget/components/icons/PlusIcon";

export default class DropOptions extends Component {
  static defaultProps = {
    items: [],
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false,
    };

    this.wrapperRef = React.createRef();
    this.handleClickOutside = this.handleClickOutside.bind(this);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
  }

  handleClickOutside(event) {
    if (this.wrapperRef && !this.wrapperRef.current.contains(event.target) && this.state.isOpen) {
      this.toggleDropdown();
    }
  }

  toggleDropdown = () => {
    const {isOpen} = this.state;
    this.setState({isOpen: !this.state.isOpen});
    if (!isOpen) {
      document.addEventListener('mousedown', this.handleClickOutside);
    } else {
      document.removeEventListener('mousedown', this.handleClickOutside);
    }
  };

  render() {
    const {isOpen} = this.state;
    const {items} = this.props;

    let i = 0;
    const renderItems = items.map((item) =>
      <span key={i++}>
        {item}
      </span>
    );

    return (
      <div className="drop-options" ref={this.wrapperRef}>
        <div className={`drop-options-list ${(isOpen) ? 'open' : ''}`}>
          {renderItems}
        </div>
        <div className="icon" onClick={this.toggleDropdown}>
          <PlusIcon className="flex-center" />
        </div>
      </div>
    )
  }
}
