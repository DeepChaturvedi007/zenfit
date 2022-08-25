import React from "react";
const TableHeadComponent = (props) => {
    const {tableTitles} = props;
    return(
        <thead className={"zenfitTableHead"}>
            <tr>
                {
                    tableTitles.map((title,index) => <td className={`thead-${index} thead-${title}`} key={index}>{title}</td>)
                }
            </tr>
        </thead>
    )

}
export default TableHeadComponent;
