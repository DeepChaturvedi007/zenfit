import React from 'react';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import axios from 'axios';

const animatedComponents = makeAnimated();

const TagFilter = (props) => {
	const { tagFilterConversation, q } = props;
	const [tagList, setTagList] = React.useState([]);

	const tagSelect = (value) => {
		let searchValue = [];
		if (value !== null) {
			searchValue = value.reduce((accumulator, currentValue) => {
				accumulator.push(currentValue.label);
				return accumulator
			}, [])
		}
		const filterData = { q, tags: searchValue }
		tagFilterConversation(filterData)
	}

	const prepareTagFilterList = options => {
		return Object.keys(options).map(key => ({
			value: options[key],
			label: _.capitalize(options[key]),
		}))
	}

	React.useEffect(() => {
		axios.get('/api/trainer/get-tags-by-user').then(({ data }) => {
			setTagList(prepareTagFilterList(data.tags))
		}).
			catch(err => {
				console.log(err)
			})
	}, [])

	return (
		<Select
			components={animatedComponents}
			isMulti
			options={tagList}
			onChange={(value) => { tagSelect(value) }}
		/>
	)
}

export default TagFilter;