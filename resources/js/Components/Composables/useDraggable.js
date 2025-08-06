import { ref, watch } from 'vue';
import useToast from '@/Utils/useToast';


export const useDraggable = (documentRequestStore) => {
    const { showToast } = useToast();
    const stages = ref([
        { id: "new request", name: "New Requests", requests: [] },
        { id: 'pending', name: 'Pending', requests: [] },
        { id: 'processing', name: 'Processing', requests: [] },
        { id: 'completed', name: 'Completed', requests: [] }
    ]);


    const getStatusColor = (status) => {
        const colors = {
            'new request': 'bg-gray-100 text-gray-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'processing': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const handleDragEnd = async (event) => {
        try {
            const movedItem = event.item.__draggable_context.element;
            const newStage = event.to.getAttribute('data-stage');

            await documentRequestStore.updateStatusDocumentRequest(movedItem.id, {
                status: newStage
            });

            showToast('Request status updated successfully', 'success');

        } catch (error) {
            console.error('Error updating document request:', error);
            showToast('Error updating document request', 'error');
        }
    };

    const updateStages = (documentRequests) => {

        console.log('Updating stages with document requests:', documentRequests);
        stages.value.forEach(stage => {
            stage.requests = documentRequests.filter(
                request => request.status === stage.id
            );
        });
    };

    return {
        stages,
        getStatusColor,
        handleDragEnd,
        updateStages
    };
}
