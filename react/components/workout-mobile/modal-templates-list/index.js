/*jshint esversion: 6 */
import React from 'react';
import OverlayConfirm from '../overlay-confirm';

export default class ModalTemplatesList extends React.Component {
    render() {
        const {templates, selectedTemplates, onSelect} = this.props;
        const isDisabled = !Object.keys(selectedTemplates).length;
        const templatesItems = templates.map(template => {
            const isSelected = selectedTemplates[template.id];
            const lastUpdated = template.updatedDate ? <p>Last updated: {template.updatedDate}</p> : null;
            return (
                <li key={template.id} onClick={onSelect.bind(null, template)}>
                    <div className="template-text">
                        <h4>{template.name}</h4>
                        {lastUpdated}
                    </div>
                    <div className={`template-status ${isSelected ? 'selected' : ''}`}>
                        <span>{isSelected ? 'Selected' : 'Select'}</span>
                    </div>
                </li>
            );
        });
        const subtitle = `${Object.keys(selectedTemplates).length} Templates Selected`;

        return (
            <OverlayConfirm {...this.props}
                            title="Use Template(s)"
                            subtitle={subtitle}
                            isDisabled={isDisabled}>
                <div className="templates-list-header"/>
                <ul className="templates-list">
                    {templatesItems}
                </ul>
            </OverlayConfirm>
        );
    }
}
