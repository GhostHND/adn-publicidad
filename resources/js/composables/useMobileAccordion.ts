import { ref } from 'vue';

export function useMobileAccordion() {
    const expandedId =
        ref<number | null>(
            null,
        );

    const toggleCard = (
        id: number,
    ): void => {
        expandedId.value =
            expandedId.value === id
                ? null
                : id;
    };

    const isExpanded = (
        id: number,
    ): boolean => {
        return expandedId.value === id;
    };

    const closeAll =
        (): void => {
            expandedId.value =
                null;
        };

    return {
        expandedId,
        toggleCard,
        isExpanded,
        closeAll,
    };
}