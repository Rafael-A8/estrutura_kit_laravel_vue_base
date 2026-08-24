/**
 * Pass-through compartilhado do `<ConfirmDialog>` do PrimeVue: mantém todas as confirmações do
 * painel com a mesma caixa. Cor e raio vêm do preset (`theme/preset.ts`); aqui ficam só largura
 * e hierarquia interna, que não são token.
 */
export const confirmDialogPt = {
    root: { class: 'max-w-[430px]' },
    header: { class: 'items-center pb-3' },
    title: { class: 'text-[17px] font-bold' },
    content: { class: 'flex items-start gap-3 pb-5' },
    footer: { class: 'justify-end gap-2.5' },
};
