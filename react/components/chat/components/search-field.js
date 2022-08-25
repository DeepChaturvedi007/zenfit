import React, {Component} from 'react';

export default class SearchField extends Component {
  constructor(props) {
    super(props);

    this.state = {
      value: '',
    };
  }

  render() {
    return (
      <div className="form-search">
        <input type="text"
               className="form-control"
               placeholder="Search..."
               value={this.state.value}
               onChange={this.handleChange}/>
        <button className="btn"
                onClick={this.handleSubmission}
                type="submit"><i className="fa fa-search" aria-hidden="true"/>
        </button>
      </div>
    )
  }

  handleChange = (event) => {
    const {action, tags} = this.props;
    const value = event.target.value;

    this.setState({value}, () => {
      if (action) {
        action({ q: value, tags });
      }
    });
  };

  handleSubmission = () => {
    const {action} = this.props;

    if (action) {
      action({ q: this.state.value.trim(), tags });
    }
  };
}
