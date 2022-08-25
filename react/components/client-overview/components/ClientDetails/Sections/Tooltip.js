import React, {useState, useEffect, Fragment} from 'react';
import moment from 'moment';
import { connect } from 'react-redux';
import * as progress from '../../../store/progress/actions';
import { valWithUnit } from '../../../helpers';

const ToolTip = ({ measuringSystem, tooltipData, updateCheckInInfo, goalType, showKcal }) => {
	const [slidIndex, setSlidIndex] = useState(0);
	const slideChange = (value) => {
		if (slidIndex + value >= 0 && slidIndex + value < tooltipData.length) {
			setSlidIndex(slidIndex + value);
			updateCheckInInfo(tooltipData[slidIndex + value][0])
		}
	}

	const content = tooltipData.content === undefined ? {} : JSON.parse(tooltipData.content);

	const values = content.sliders ? content.sliders : null;

	let numbers = [];
	numbers.push({
		name:'Weight',
		type: measuringSystem === 1 ? "kg" : "lbs",
		val: tooltipData.weight,
		diff: tooltipData.diffWeight
	})

	useEffect(() => {
		setSlidIndex(0)
		return () => {
			setSlidIndex(0)
		}
	}, [tooltipData])

	const numberClassStyling = (item) => {
		let styleName = '';

		goalType === 1
			/*Loose weight*/
			? item.diff < 0
				? styleName = "success" :
			  item.diff > 0.2
				? styleName = "noSuccess" :
				  styleName = "noProgress"
			/*Gain weight*/
			: item.diff > 0
				? styleName = "success" :
			  item.diff < 0.2
				? styleName = "noSuccess" :
				  styleName = "noProgress"

		return styleName

	}

	const NumberContent = ({item}) => {
		return(
			<div className={"weightNumber"}>
				<span>
					{item.name} ({item.type})
				</span>
				<span className={"number"}>
					<span className="main">{item.val}</span>
					{
						(parseFloat(item.diff) !== parseFloat(item.val) || parseFloat(item.diff) === 0) && (
							<span className={"diff " + numberClassStyling(item) }>
								{item.diff > 0 ? `+${item.diff}` : item.diff }
							</span>
						)
					}

				</span>
			</div>
		)
	}

	return (
		<div className="tooltip-content">
			{tooltipData.length === 0 ? (
				<div className='tooltip-empty'>No Data</div>
			) : (
				<React.Fragment>
					<div className='tooltipHeader'>
						<div className='tooltip-header-title' >{moment(tooltipData.date).format('MMM DD, YYYY')}</div>
					</div>
					<div className='tooltipNumbers'>
						{
							tooltipData.diffWeight !== null && tooltipData.weight !== null
							&& (
								numbers.map((item,index) => {
									return(
										<Fragment key={index}>
											<NumberContent item={item}/>
										</Fragment>
									)
								})
							)
						}

					</div>
					<div className="tooltip-body-footer">
						<span className={"kcal"}>
							{tooltipData.kcal !== null && showKcal && (
								<Fragment>
									<div className='number'>
										{tooltipData.kcal}
									</div>
									kcal
								</Fragment>

							)}
						</span>
					</div>
				</React.Fragment>
			)}
		</div>
	)
}
function mapStateToProps(state) {
	return {
		clientProgress: state.progress.checkInInfo,
		goalType: state.clients.selectedClient.info.goalType
	}
}

export default connect(mapStateToProps, { ...progress })(ToolTip);
