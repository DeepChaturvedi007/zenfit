import axios from 'axios';
export const statusAction = (client, userId, eventName, id, locale, action) => {
    let returnValue = null;
    switch(eventName){
        case 'client.updated_bodyprogress':
        case 'client.uploaded_image': {
            window.open(`/progress/client/${client.id}`, '_blank');
            break;
        }
        case 'trainer.create_workout_plan':
        case 'trainer.update_workout_plan': {
            window.open(`/workout/clients/${client.id}`, '_blank');
            break;
        }
        case 'trainer.create_meal_plan':
        case 'trainer.update_meal_plan': {
            window.open(`/meal/clients/${client.id}`, '_blank');
            break;
        }
        case 'client.missing_checkin': {
            window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 12, action: false}, client.messages.id);
            break;
        }
        case 'client.payment_failed': {
            window.openChatTemplate(8, client.id);
            break;
        }
        case 'client.need_welcome':{
            window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 13, action: true}, client.messages.id);
            break;
        }
        case 'client.missing_communication':
        case 'client.sent_message':
        {
            window.openChatWidget(userId, client.id, client.name, client.photo, locale, client.messages.id);
            break;
        }
        case 'client.ending_soon': {
            if(action === 'Extend'){
                returnValue = {
                    flg: 'extend'
                }
            }
            else {
                window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 9, action: true}, client.messages.id);
            }
            break;
        }
        case 'client.completed':
        {
            if(action === 'Extend'){
                returnValue = {
                    flg: 'extend'
                }
            }
            else {
                window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 9, action: true}, client.messages.id);
            }
            break;
        }
        case 'client.subscription_canceled':
        {
            if(action === 'Resubscribe'){
                returnValue = {
                    flg: 'resubscribe'
                }
            }
            else {
                window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 9, action: true}, client.messages.id);
            }
            break;
        }
        case 'client.payment_pending': {
            // $.openSideContainer('payment-email', client.queue.id, true, false);
            window.openSideContent(true, client, 1, 'payment-email')
            break;
        }
        case 'client.invite_pending': {
            // $.openSideContainer('welcome-email', client.queue.id, true, false);
            window.openSideContent(true, client, 4, 'welcome-email')
            break;
        }
        case 'client.wrong_email': {
            if(client.queue.payment){
                // $.openSideContainer('payment-email', client.queue.id, true, false);
                window.openSideContent(true, client, 1, 'payment-email')
            } else {
                // $.openSideContainer('welcome-email', client.queue.id, true, false);
                window.openSideContent(true, client, 4, 'welcome-email')
            }
            break;
        }
        case 'client.questionnaire_pending': {
            const data = new FormData();
            data.append('client', client.id)
            axios.post('/api/client-status/resendQuestionnaire', data).then(res => {
                window.toastr.success("Questionnaire resent");
            })
            break;
        }
        default: {
            returnValue = {
                flg: 'reminder'
            }
            break;
        }
    }
    return returnValue;
}

export const updateText = (eventName) => {
    let update_text = ''
    switch(eventName){
        case 'client.updated_bodyprogress': {
            update_text = ['Reply'];
            break;
        }
        case 'client.uploaded_image': {
            update_text = ['Reply'];
            break;
        }
        case 'trainer.create_workout_plan': {
            update_text = ['Create'];
            break;
        }
        case 'trainer.update_workout_plan': {
            update_text = ['Update'];
            break;
        }
        case 'trainer.create_meal_plan': {
            update_text = ['Create'];
            break;
        }
        case 'trainer.update_meal_plan': {
            update_text = ['Update'];
            break;
        }
        case 'client.missing_communication': {
            update_text = ['Write'];
            break;
        }
        case 'client.sent_message': {
            update_text = ['Write'];
            break;
        }
        case 'client.need_welcome': {
            update_text = ['Activate'];
            break;
        }
        case 'client.missing_checkin': {
            update_text = ['Remind'];
            break;
        }
        case 'client.payment_failed': {
            update_text = ['Prompt'];
            break;
        }
        case 'client.ending_soon': {
            update_text = ['Extend', 'Say Goodbye'];
            break;
        }
        case 'client.completed': {
            update_text = ['Extend', 'Say Goodbye'];
            break;
        }
        case 'client.subscription_canceled': {
            update_text = ['Resubscribe', 'Say Goodbye'];
            break;
        }
        case 'client.payment_pending': {
            update_text = ['Resend'];
            break;
        }
        case 'client.invite_pending': {
            update_text = ['Resend'];
            break;
        }
        case 'client.wrong_email': {
            update_text = ['Change'];
            break;
        }
        case 'client.questionnaire_pending': {
            update_text = ['Resend'];
            break;
        }
        default: {
            update_text = ['Resolve'];
            break;
        }
    }
    return update_text
}
