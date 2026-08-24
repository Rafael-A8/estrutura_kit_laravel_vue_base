import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

/**
 * Preset base do PrimeVue para o painel.
 *
 * Ele NÃO define uma paleta própria: aponta os tokens semânticos do PrimeVue para as CSS
 * variables do tema (as mesmas de `resources/css/app.css`, usadas pelo Tailwind e pelos
 * componentes shadcn). Consequência prática: mudar `--primary`/`--border` no CSS repinta o
 * PrimeVue junto, e o modo escuro funciona sem um segundo mapa de cores — a classe `.dark`
 * já troca o valor por trás de cada `var()`.
 *
 * Ajuste de identidade visual acontece em `app.css`. Aqui só entram tokens que não existem
 * do lado shadcn (geometria de componente, peso de fonte de cabeçalho, etc).
 */
const colorScheme = {
    primary: {
        color: 'var(--primary)',
        contrastColor: 'var(--primary-foreground)',
        hoverColor:
            'color-mix(in oklab, var(--primary) 88%, var(--background))',
        activeColor:
            'color-mix(in oklab, var(--primary) 78%, var(--background))',
    },
    highlight: {
        background: 'var(--accent)',
        focusBackground: 'var(--accent)',
        color: 'var(--accent-foreground)',
        focusColor: 'var(--accent-foreground)',
    },
    content: {
        background: 'var(--card)',
        hoverBackground: 'var(--accent)',
        borderColor: 'var(--border)',
        color: 'var(--card-foreground)',
        hoverColor: 'var(--accent-foreground)',
    },
    text: {
        color: 'var(--foreground)',
        hoverColor: 'var(--foreground)',
        mutedColor: 'var(--muted-foreground)',
        hoverMutedColor: 'var(--foreground)',
    },
    overlay: {
        select: {
            background: 'var(--popover)',
            borderColor: 'var(--border)',
            color: 'var(--popover-foreground)',
        },
        popover: {
            background: 'var(--popover)',
            borderColor: 'var(--border)',
            color: 'var(--popover-foreground)',
        },
        modal: {
            background: 'var(--popover)',
            borderColor: 'var(--border)',
            color: 'var(--popover-foreground)',
        },
    },
    list: {
        option: {
            focusBackground: 'var(--accent)',
            selectedBackground: 'var(--accent)',
            selectedFocusBackground: 'var(--accent)',
            color: 'var(--foreground)',
            focusColor: 'var(--accent-foreground)',
            selectedColor: 'var(--accent-foreground)',
            selectedFocusColor: 'var(--accent-foreground)',
        },
    },
    formField: {
        background: 'var(--background)',
        disabledBackground: 'var(--muted)',
        filledBackground: 'var(--muted)',
        filledFocusBackground: 'var(--muted)',
        borderColor: 'var(--input)',
        hoverBorderColor: 'var(--ring)',
        focusBorderColor: 'var(--ring)',
        invalidBorderColor: 'var(--destructive)',
        color: 'var(--foreground)',
        disabledColor: 'var(--muted-foreground)',
        placeholderColor: 'var(--muted-foreground)',
        floatLabelColor: 'var(--muted-foreground)',
        iconColor: 'var(--muted-foreground)',
        shadow: 'none',
    },
    navigation: {
        item: {
            focusBackground: 'var(--accent)',
            activeBackground: 'var(--accent)',
            color: 'var(--foreground)',
            focusColor: 'var(--accent-foreground)',
            activeColor: 'var(--accent-foreground)',
            icon: {
                color: 'var(--muted-foreground)',
                focusColor: 'var(--foreground)',
                activeColor: 'var(--foreground)',
            },
        },
    },
};

const Preset = definePreset(Aura, {
    primitive: {
        borderRadius: {
            none: '0',
            xs: 'calc(var(--radius) - 6px)',
            sm: 'var(--radius-sm)',
            md: 'var(--radius-md)',
            lg: 'var(--radius-lg)',
            xl: 'calc(var(--radius) + 4px)',
        },
    },
    semantic: {
        focusRing: {
            width: '2px',
            style: 'solid',
            color: 'var(--ring)',
            offset: '2px',
        },
        colorScheme: {
            light: colorScheme,
            dark: colorScheme,
        },
    },
    components: {
        datatable: {
            columnTitle: {
                fontWeight: '600',
            },
            colorScheme: {
                light: {
                    headerCell: {
                        background: 'var(--muted)',
                        color: 'var(--muted-foreground)',
                        borderColor: 'var(--border)',
                    },
                    bodyCell: { borderColor: 'var(--border)' },
                    root: { borderColor: 'transparent' },
                    paginatorBottom: { borderColor: 'transparent' },
                },
                dark: {
                    headerCell: {
                        background: 'var(--muted)',
                        color: 'var(--muted-foreground)',
                        borderColor: 'var(--border)',
                    },
                    bodyCell: { borderColor: 'var(--border)' },
                    root: { borderColor: 'transparent' },
                    paginatorBottom: { borderColor: 'transparent' },
                },
            },
        },
        dialog: {
            title: {
                fontSize: '18px',
                fontWeight: '700',
            },
        },
        /**
         * A barra de filtros do painel é uma linha nua sobre o fundo da página, sem cartão.
         * Zerado aqui, e não por classe Tailwind na tela: o PrimeVue injeta `.p-toolbar` em
         * runtime com a mesma especificidade e a regra dele venceria por vir depois.
         */
        toolbar: {
            root: {
                background: 'transparent',
                borderColor: 'transparent',
                padding: '0',
            },
        },
    },
});

export default Preset;
