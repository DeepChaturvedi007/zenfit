import React, {PureComponent} from 'react';
import ReactTags from 'react-tag-autocomplete';
import stringScore from 'string-score';
import includes from 'lodash/includes';

export default class TagsField extends PureComponent {
  constructor(props) {
    super(props);

    this.state = {
      tags: [],
    };
  }

  handleDelete = (index) => {
    const tags = this.props.tags.slice(0);

    tags.splice(index, 1);
    this.handleUpdate(tags);
  };

  handleAddition = (tag) => {
    const tags = [].concat(this.props.tags, tag);
    this.handleUpdate(tags);
  };

  handleUpdate = (tags) => {
    const {action} = this.props;

    if (action) {
      action({tags});
    }
  };

  suggestionFilter = (item, query) => {
    const score = stringScore(item.name, query);
    return score > 0.5;
  };

  render() {
    const {tags} = this.props;
    const selected = tags.map(tag => tag.id);

    const suggestions = this.props.suggestions
      .filter(item => !includes(selected, item.id));

    return (
      <div className="form-search">
        <ReactTags
          autoresize={false}
          tags={tags}
          placeholder="Filter by tags"
          suggestions={suggestions}
          suggestionsFilter={this.suggestionFilter}
          handleDelete={this.handleDelete}
          handleAddition={this.handleAddition}/>
      </div>
    );
  }
}

TagsField.defaultProps = {
  tags: [],
};
