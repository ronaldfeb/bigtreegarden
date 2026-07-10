import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';

const root = new URL('..', import.meta.url).pathname;
const routesRoot = join(root, 'resources/js/routes');
const pagesRoot = join(root, 'resources/js/pages/staff');

function makeRouteFile({ importDepth, baseUrl, param }) {
    const wayfinderImport = `${'../'.repeat(importDepth)}wayfinder`;
    const hasParam = Boolean(param);

    const paramType = hasParam
        ? `args: { ${param}: string | number | { id: string | number } } | [${param}: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions`
        : 'options?: RouteQueryOptions';

    const paramUrlBody = hasParam
        ? `
    if (typeof args === 'string' || typeof args === 'number') {
        args = { ${param}: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { ${param}: args.id }
    }

    if (Array.isArray(args)) {
        args = { ${param}: args[0] }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        ${param}: typeof args.${param} === 'object' ? args.${param}.id : args.${param},
    }

    return definition.url
        .replace('{${param}}', parsedArgs.${param}.toString())
        .replace(/\\/+$/, '') + queryParams(options)`
        : `return definition.url + queryParams(options)`;

    const actions = hasParam
        ? ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']
        : ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    const urls = {
        index: baseUrl,
        create: `${baseUrl}/create`,
        store: baseUrl,
        show: hasParam ? `${baseUrl}/{${param}}` : baseUrl,
        edit: hasParam ? `${baseUrl}/{${param}}/edit` : `${baseUrl}/edit`,
        update: hasParam ? `${baseUrl}/{${param}}` : baseUrl,
        destroy: hasParam ? `${baseUrl}/{${param}}` : baseUrl,
    };

    const methods = {
        index: ['get', 'head'],
        create: ['get', 'head'],
        store: ['post'],
        show: ['get', 'head'],
        edit: ['get', 'head'],
        update: ['patch'],
        destroy: ['delete'],
    };

    let content = `import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from '${wayfinderImport}'\n`;

    for (const action of actions) {
        const methodList = methods[action];
        const url = urls[action];
        const httpMethod = methodList[0];
        const usesParam = ['show', 'edit', 'update', 'destroy'].includes(action) && hasParam;
        const fnArgs = usesParam ? paramType : 'options?: RouteQueryOptions';
        const fnCall = usesParam ? 'args, options' : 'options';

        content += `
export const ${action} = (${fnArgs}): RouteDefinition<'${httpMethod}'> => ({
    url: ${action}.url(${fnCall}),
    method: '${httpMethod}',
})

const definition = {
    methods: ${JSON.stringify(methodList)},
    url: '${url}',
} satisfies RouteDefinition<${JSON.stringify(methodList)}>

${action}.definition = definition

${action}.url = (${fnArgs}) => {
${usesParam ? paramUrlBody : '    return definition.url + queryParams(options)'}
}

${action}.get = (${fnArgs}): RouteDefinition<'get'> => ({
    url: ${action}.url(${fnCall}),
    method: 'get',
})

const ${action}Form = (${fnArgs}): RouteFormDefinition<'${httpMethod === 'get' || httpMethod === 'head' ? 'get' : 'post'}'> => ({
    action: ${action}.url(${fnCall}${action === 'update' || action === 'destroy' ? `, action === 'update' || action === 'destroy' ? undefined : undefined` : ''}),
    method: '${['patch', 'delete'].includes(httpMethod) ? 'post' : httpMethod}',
})

${action}.form = ${action}Form
`;
    }

    return content;
}

function writeRoute(resource) {
    const dir = join(routesRoot, resource.path);
    mkdirSync(dir, { recursive: true });
    const depth = resource.path.split('/').length + 1;
    writeFileSync(
        join(dir, 'index.ts'),
        makeRouteFile({
            importDepth: depth,
            baseUrl: resource.baseUrl,
            param: resource.param,
        }),
    );
}

const resources = [
    { path: 'staff', baseUrl: '/staff', param: null, pagePath: null },
    {
        path: 'staff/marketing/adverts',
        baseUrl: '/staff/marketing/adverts',
        param: 'advert',
        pagePath: 'marketing/adverts',
        singular: 'Advert',
        plural: 'adverts',
        title: 'Adverts',
        prop: 'advert',
    },
    {
        path: 'staff/marketing/leads',
        baseUrl: '/staff/marketing/leads',
        param: 'lead',
        pagePath: 'marketing/leads',
        singular: 'Lead',
        plural: 'leads',
        title: 'Leads',
        prop: 'lead',
    },
    {
        path: 'staff/content/blogs',
        baseUrl: '/staff/content/blogs',
        param: 'blog',
        pagePath: 'content/blogs',
        singular: 'Blog',
        plural: 'blogs',
        title: 'Blogs',
        prop: 'blog',
    },
    {
        path: 'staff/content/blog-categories',
        baseUrl: '/staff/content/blog-categories',
        param: 'blog_category',
        pagePath: 'content/blog-categories',
        singular: 'Blog category',
        plural: 'categories',
        title: 'Blog categories',
        prop: 'category',
        listProp: 'categories',
    },
    {
        path: 'staff/content/help-center-topics',
        baseUrl: '/staff/content/help-center-topics',
        param: 'help_center_topic',
        pagePath: 'content/help-center-topics',
        singular: 'Help topic',
        plural: 'topics',
        title: 'Help center topics',
        prop: 'topic',
        listProp: 'topics',
    },
    {
        path: 'staff/content/help-center-categories',
        baseUrl: '/staff/content/help-center-categories',
        param: 'help_center_category',
        pagePath: 'content/help-center-categories',
        singular: 'Help category',
        plural: 'categories',
        title: 'Help center categories',
        prop: 'category',
        listProp: 'categories',
    },
    {
        path: 'staff/content/help-center-articles',
        baseUrl: '/staff/content/help-center-articles',
        param: 'help_center_article',
        pagePath: 'content/help-center-articles',
        singular: 'Help article',
        plural: 'articles',
        title: 'Help center articles',
        prop: 'article',
        listProp: 'articles',
    },
    {
        path: 'staff/content/policies',
        baseUrl: '/staff/content/policies',
        param: 'policy',
        pagePath: 'content/policies',
        singular: 'Policy',
        plural: 'policies',
        title: 'Policies',
        prop: 'policy',
    },
    {
        path: 'staff/content/testimonials',
        baseUrl: '/staff/content/testimonials',
        param: 'testimonial',
        pagePath: 'content/testimonials',
        singular: 'Testimonial',
        plural: 'testimonials',
        title: 'Testimonials',
        prop: 'testimonial',
    },
    {
        path: 'staff/content/partners',
        baseUrl: '/staff/content/partners',
        param: 'partner',
        pagePath: 'content/partners',
        singular: 'Partner',
        plural: 'partners',
        title: 'Partners',
        prop: 'partner',
    },
    {
        path: 'staff/content/ambassadors',
        baseUrl: '/staff/content/ambassadors',
        param: 'ambassador',
        pagePath: 'content/ambassadors',
        singular: 'Ambassador',
        plural: 'ambassadors',
        title: 'Ambassadors',
        prop: 'ambassador',
    },
    {
        path: 'staff/content/pamphlet-background-collections',
        baseUrl: '/staff/content/pamphlet-background-collections',
        param: 'pamphlet_background_collection',
        pagePath: 'content/pamphlet-background-collections',
        singular: 'Pamphlet collection',
        plural: 'collections',
        title: 'Pamphlet background collections',
        prop: 'collection',
        listProp: 'collections',
    },
    {
        path: 'staff/content/pamphlet-backgrounds',
        baseUrl: '/staff/content/pamphlet-backgrounds',
        param: 'pamphlet_background',
        pagePath: 'content/pamphlet-backgrounds',
        singular: 'Pamphlet background',
        plural: 'backgrounds',
        title: 'Pamphlet backgrounds',
        prop: 'background',
    },
    {
        path: 'staff/commerce/subscription-packages',
        baseUrl: '/staff/commerce/subscription-packages',
        param: 'subscription_package',
        pagePath: 'commerce/subscription-packages',
        singular: 'Subscription package',
        plural: 'packages',
        title: 'Subscription packages',
        prop: 'package',
        listProp: 'packages',
    },
    {
        path: 'staff/commerce/transactions',
        baseUrl: '/staff/commerce/transactions',
        param: 'transaction',
        pagePath: 'commerce/transactions',
        singular: 'Transaction',
        plural: 'transactions',
        title: 'Transactions',
        prop: 'transaction',
        readOnly: true,
    },
    {
        path: 'staff/directory/service-providers',
        baseUrl: '/staff/directory/service-providers',
        param: 'service_provider',
        pagePath: 'directory/service-providers',
        singular: 'Service provider',
        plural: 'serviceProviders',
        title: 'Service providers',
        prop: 'serviceProvider',
        listProp: 'serviceProviders',
    },
    {
        path: 'staff/directory/users',
        baseUrl: '/staff/directory/users',
        param: 'user',
        pagePath: 'directory/users',
        singular: 'User',
        plural: 'users',
        title: 'Users',
        prop: 'user',
    },
    {
        path: 'staff/directory/persons-of-interest',
        baseUrl: '/staff/directory/persons-of-interest',
        param: 'person_of_interest',
        pagePath: 'directory/persons-of-interest',
        singular: 'Person of interest',
        plural: 'personsOfInterest',
        title: 'Persons of interest',
        prop: 'personOfInterest',
        listProp: 'personsOfInterest',
    },
];

// Simpler route generator
function writeSimpleRoutes() {
    mkdirSync(join(routesRoot, 'staff'), { recursive: true });
    writeFileSync(
        join(routesRoot, 'staff/index.ts'),
        `import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'

export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = { methods: ['get', 'head'], url: '/staff' } satisfies RouteDefinition<['get', 'head']>
dashboard.url = (options?: RouteQueryOptions) => dashboard.definition.url + queryParams(options)
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({ url: dashboard.url(options), method: 'get' })
dashboard.form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({ action: dashboard.url(options), method: 'get' })
`,
    );

    for (const resource of resources.filter((r) => r.pagePath)) {
        const depth = resource.path.split('/').length;
        const wayfinder = `${'../'.repeat(depth)}wayfinder`;
        const p = resource.param;
        const base = resource.baseUrl;

        const bind = (action, url, methods, formMethod = null) => {
            const needsParam = url.includes(`{${p}}`);
            const args = needsParam
                ? `args: { ${p}: string | number | { id: string | number } } | string | number | { id: string | number }, options?: RouteQueryOptions`
                : 'options?: RouteQueryOptions';
            const call = needsParam ? 'args, options' : 'options';
            const resolve = needsParam
                ? `let a = args as any; if (typeof a === 'string' || typeof a === 'number') a = { ${p}: a }; if (a && typeof a === 'object' && 'id' in a && !('${p}' in a)) a = { ${p}: a.id }; const id = typeof a.${p} === 'object' ? a.${p}.id : a.${p}; return '${url}'.replace('{${p}}', String(id)) + queryParams(options)`
                : `return '${url}' + queryParams(options)`;

            const formAction = formMethod
                ? `${action}.url(${needsParam ? 'args' : ''}${needsParam ? ', ' : ''}{ ...(options ?? {}), query: { _method: '${formMethod.toUpperCase()}', ...(options?.query ?? {}) } })`
                : `${action}.url(${call})`;

            return `
export const ${action} = (${args}): RouteDefinition<'${methods[0]}'> => ({ url: ${action}.url(${call}), method: '${methods[0]}' })
${action}.url = (${args}) => { ${resolve} }
${action}.form = (${args}) => ({ action: ${formAction}, method: '${formMethod ? 'post' : methods[0]}' })`;
        };

        let content = `import { queryParams, type RouteQueryOptions, type RouteDefinition } from '${wayfinder}'\n`;

        if (resource.readOnly) {
            content += bind('index', base, ['get']);
            content += bind('show', `${base}/{${p}}`, ['get']);
        } else {
            content += bind('index', base, ['get']);
            content += bind('create', `${base}/create`, ['get']);
            content += bind('store', base, ['post']);
            content += bind('show', `${base}/{${p}}`, ['get']);
            content += bind('edit', `${base}/{${p}}/edit`, ['get']);
            content += bind('update', `${base}/{${p}}`, ['patch'], 'patch');
            content += bind('destroy', `${base}/{${p}}`, ['delete'], 'delete');
        }

        const dir = join(routesRoot, resource.path);
        mkdirSync(dir, { recursive: true });
        writeFileSync(join(dir, 'index.ts'), content);
    }

    mkdirSync(join(routesRoot, 'staff/marketing/leads/notes'), { recursive: true });
    writeFileSync(
        join(routesRoot, 'staff/marketing/leads/notes/index.ts'),
        `import { queryParams, type RouteQueryOptions } from './../../../../../wayfinder'

const resolveLead = (lead: string | number | { id: string | number }) => {
    if (typeof lead === 'object' && 'id' in lead) return String(lead.id)
    return String(lead)
}

const resolveNote = (note: string | number | { id: string | number }) => {
    if (typeof note === 'object' && 'id' in note) return String(note.id)
    return String(note)
}

export const store = (lead: string | number | { id: string | number }, options?: RouteQueryOptions) => ({
    url: '/staff/marketing/leads/' + resolveLead(lead) + '/notes' + queryParams(options),
    method: 'post' as const,
})

store.form = (lead: string | number | { id: string | number }, options?: RouteQueryOptions) => ({
    action: store(lead, options).url,
    method: 'post' as const,
})

export const destroy = (
    lead: string | number | { id: string | number },
    note: string | number | { id: string | number },
    options?: RouteQueryOptions,
) => ({
    url: '/staff/marketing/leads/' + resolveLead(lead) + '/notes/' + resolveNote(note) + queryParams(options),
    method: 'delete' as const,
})

destroy.form = (
    lead: string | number | { id: string | number },
    note: string | number | { id: string | number },
    options?: RouteQueryOptions,
) => ({
    action: destroy(lead, note, { ...options, query: { ...(options?.query ?? {}), _method: 'DELETE' } }).url,
    method: 'post' as const,
})
`,
    );
}

writeSimpleRoutes();

function pageImports(resource, variant = 'full') {
    if (variant === 'index') {
        return `import { Head } from '@inertiajs/vue3';
import StaffDataTable from '@/components/staff/StaffDataTable.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { ${resource.readOnly ? 'index, show' : 'index, create, show, edit'} } from '@/routes/${resource.path}';
import type { Paginated } from '@/types/staff';`;
    }

    if (variant === 'show') {
        return `import { Form, Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, ${resource.readOnly ? 'show' : 'edit, destroy, show'} } from '@/routes/${resource.path}';`;
    }

    return `import { Head, Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import StaffFormActions from '@/components/staff/StaffFormActions.vue';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index, show, store, update } from '@/routes/${resource.path}';`;
}

const indexColumns = {
    'marketing/adverts': [
        { key: 'client_name', label: 'Client' },
        { key: 'campaign_name', label: 'Campaign' },
        { key: 'status', label: 'Status' },
        { key: 'visits_count', label: 'Visits' },
    ],
    'marketing/leads': [
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'source', label: 'Source' },
        { key: 'status', label: 'Status' },
    ],
    'content/blogs': [
        { key: 'title', label: 'Title' },
        { key: 'status', label: 'Status' },
        { key: 'published_at', label: 'Published' },
    ],
    'content/blog-categories': [
        { key: 'name', label: 'Name' },
        { key: 'sort_order', label: 'Order' },
        { key: 'is_active', label: 'Active' },
    ],
    'content/help-center-topics': [
        { key: 'name', label: 'Name' },
        { key: 'sort_order', label: 'Order' },
        { key: 'is_active', label: 'Active' },
    ],
    'content/help-center-categories': [
        { key: 'name', label: 'Name' },
        { key: 'sort_order', label: 'Order' },
        { key: 'is_active', label: 'Active' },
    ],
    'content/help-center-articles': [
        { key: 'title', label: 'Title' },
        { key: 'status', label: 'Status' },
        { key: 'published_at', label: 'Published' },
    ],
    'content/policies': [
        { key: 'title', label: 'Title' },
        { key: 'type', label: 'Type' },
        { key: 'version', label: 'Version' },
    ],
    'content/testimonials': [
        { key: 'name', label: 'Name' },
        { key: 'rating', label: 'Rating' },
        { key: 'status', label: 'Status' },
    ],
    'content/partners': [
        { key: 'name', label: 'Name' },
        { key: 'website_url', label: 'Website' },
        { key: 'is_active', label: 'Active' },
    ],
    'content/ambassadors': [
        { key: 'name', label: 'Name' },
        { key: 'title', label: 'Title' },
        { key: 'status', label: 'Status' },
    ],
    'content/pamphlet-background-collections': [
        { key: 'name', label: 'Name' },
        { key: 'sort_order', label: 'Order' },
        { key: 'is_active', label: 'Active' },
    ],
    'content/pamphlet-backgrounds': [
        { key: 'name', label: 'Name' },
        { key: 'collection_id', label: 'Collection' },
        { key: 'is_active', label: 'Active' },
    ],
    'commerce/subscription-packages': [
        { key: 'name', label: 'Name' },
        { key: 'price_cents', label: 'Price (cents)' },
        { key: 'billing_interval', label: 'Billing' },
    ],
    'commerce/transactions': [
        { key: 'reference', label: 'Reference' },
        { key: 'amount_cents', label: 'Amount (cents)' },
        { key: 'status', label: 'Status' },
    ],
    'directory/service-providers': [
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'status', label: 'Status' },
    ],
    'directory/users': [
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'phone', label: 'Phone' },
    ],
    'directory/persons-of-interest': [
        { key: 'display_name', label: 'Name' },
        { key: 'date_of_birth', label: 'Born' },
        { key: 'status', label: 'Status' },
    ],
};

function writeIndex(resource) {
    const listProp = resource.listProp ?? resource.plural;
    const columns = indexColumns[resource.pagePath] ?? [{ key: 'id', label: 'ID' }];
    const columnsJson = JSON.stringify(columns, null, 4).replace(/^/gm, '    ');

    const content = `<script setup lang="ts">
${pageImports(resource, 'index')}

defineProps<{
    ${listProp}: Paginated<Record<string, unknown>>;
}>();

const columns = ${columnsJson.trimStart()};
</script>

<template>
    <StaffLayout>
        <Head title="${resource.title}" />

        <StaffPageHeader
            title="${resource.title}"
            description="Manage ${resource.title.toLowerCase()}."
            ${resource.readOnly ? '' : ':create-href="create()" create-label="Create"'}
        />

        <StaffDataTable
            :columns="columns"
            :paginator="${listProp}"
            :row-actions="[{ label: 'View', href: (row) => show(row.id) }${resource.readOnly ? '' : `, { label: 'Edit', href: (row) => edit(row.id) }`}]"
        />
    </StaffLayout>
</template>
`;

    writeFileSync(join(pagesRoot, resource.pagePath, 'Index.vue'), content);
}

function fieldInput(field, modelExpr = null) {
    const value = modelExpr ? `:default-value="${modelExpr}.${field.name}"` : '';

    if (field.type === 'textarea') {
        return `<div class="grid gap-2">
    <Label for="${field.name}">${field.label}</Label>
    <Textarea id="${field.name}" name="${field.name}" rows="8" ${value} ${field.required ? 'required' : ''} />
    <InputError :message="errors.${field.name}" />
</div>`;
    }

    if (field.type === 'select') {
        const options = field.options
            .map((o) => `<option value="${o.value}">${o.label}</option>`)
            .join('\n            ');

        return `<div class="grid gap-2">
    <Label for="${field.name}">${field.label}</Label>
    <select id="${field.name}" name="${field.name}" class="border-input h-10 w-full rounded-[var(--radius)] border bg-transparent px-3 text-sm" ${field.required ? 'required' : ''}>
        ${options}
    </select>
    <InputError :message="errors.${field.name}" />
</div>`;
    }

    if (field.type === 'checkbox') {
        const checked = modelExpr
            ? `:checked="Boolean(${modelExpr}.${field.name})"`
            : '';

        return `<div class="flex items-center gap-2">
    <input type="hidden" name="${field.name}" value="0" />
    <input id="${field.name}" name="${field.name}" type="checkbox" value="1" class="size-4 rounded border-input" ${checked} />
    <Label for="${field.name}">${field.label}</Label>
    <InputError :message="errors.${field.name}" />
</div>`;
    }

    return `<div class="grid gap-2">
    <Label for="${field.name}">${field.label}</Label>
    <Input id="${field.name}" name="${field.name}" type="${field.type ?? 'text'}" ${value} ${field.required ? 'required' : ''} />
    <InputError :message="errors.${field.name}" />
</div>`;
}

const formFields = {
    'marketing/adverts': [
        { name: 'client_name', label: 'Client name', required: true },
        { name: 'campaign_name', label: 'Campaign name' },
        { name: 'destination_url', label: 'Destination URL', type: 'url', required: true },
        { name: 'utm_source', label: 'UTM source', required: true },
        { name: 'utm_medium', label: 'UTM medium' },
        { name: 'utm_campaign', label: 'UTM campaign', required: true },
        { name: 'utm_content', label: 'UTM content' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            required: true,
            options: [
                { value: 'active', label: 'Active' },
                { value: 'paused', label: 'Paused' },
                { value: 'archived', label: 'Archived' },
            ],
        },
        { name: 'starts_at', label: 'Starts at', type: 'datetime-local' },
        { name: 'ends_at', label: 'Ends at', type: 'datetime-local' },
    ],
    'marketing/leads': [
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email' },
        { name: 'phone', label: 'Phone' },
        { name: 'organisation', label: 'Organisation' },
        {
            name: 'source',
            label: 'Source',
            type: 'select',
            required: true,
            options: [
                { value: 'advert', label: 'Advert' },
                { value: 'website', label: 'Website' },
                { value: 'referral', label: 'Referral' },
                { value: 'walk_in', label: 'Walk in' },
                { value: 'other', label: 'Other' },
            ],
        },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'new', label: 'New' },
                { value: 'contacted', label: 'Contacted' },
                { value: 'qualified', label: 'Qualified' },
                { value: 'converted', label: 'Converted' },
                { value: 'lost', label: 'Lost' },
            ],
        },
        { name: 'marketing_advert_id', label: 'Advert ID' },
    ],
    'content/blogs': [
        { name: 'title', label: 'Title', required: true },
        { name: 'blog_category_id', label: 'Category ID' },
        { name: 'excerpt', label: 'Excerpt', type: 'textarea' },
        { name: 'body', label: 'Body', type: 'textarea', required: true },
        { name: 'cover_image_path', label: 'Cover image path' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            required: true,
            options: [
                { value: 'draft', label: 'Draft' },
                { value: 'published', label: 'Published' },
            ],
        },
        { name: 'published_at', label: 'Published at', type: 'datetime-local' },
        { name: 'meta_title', label: 'Meta title' },
        { name: 'meta_description', label: 'Meta description' },
    ],
    'content/blog-categories': [
        { name: 'name', label: 'Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'content/help-center-topics': [
        { name: 'name', label: 'Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'icon', label: 'Icon' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'content/help-center-categories': [
        { name: 'name', label: 'Name', required: true },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'content/help-center-articles': [
        { name: 'help_center_topic_id', label: 'Topic ID', required: true },
        { name: 'title', label: 'Title', required: true },
        { name: 'excerpt', label: 'Excerpt', type: 'textarea' },
        { name: 'body', label: 'Body', type: 'textarea', required: true },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            required: true,
            options: [
                { value: 'draft', label: 'Draft' },
                { value: 'published', label: 'Published' },
            ],
        },
        { name: 'published_at', label: 'Published at', type: 'datetime-local' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
    ],
    'content/policies': [
        {
            name: 'type',
            label: 'Type',
            type: 'select',
            required: true,
            options: [
                { value: 'terms_of_service', label: 'Terms of service' },
                { value: 'privacy_policy', label: 'Privacy policy' },
                { value: 'about_us', label: 'About us' },
            ],
        },
        { name: 'title', label: 'Title', required: true },
        { name: 'body', label: 'Body', type: 'textarea', required: true },
        { name: 'version', label: 'Version' },
        { name: 'published_at', label: 'Published at', type: 'datetime-local' },
    ],
    'content/testimonials': [
        { name: 'name', label: 'Name', required: true },
        { name: 'photo_path', label: 'Photo path', required: true },
        { name: 'role_or_location', label: 'Role or location' },
        { name: 'body', label: 'Body', type: 'textarea', required: true },
        { name: 'rating', label: 'Rating', type: 'number' },
        { name: 'is_featured', label: 'Featured', type: 'checkbox' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'draft', label: 'Draft' },
                { value: 'published', label: 'Published' },
            ],
        },
    ],
    'content/partners': [
        { name: 'name', label: 'Name', required: true },
        { name: 'logo_path', label: 'Logo path', required: true },
        { name: 'website_url', label: 'Website URL', type: 'url' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'content/ambassadors': [
        { name: 'name', label: 'Name', required: true },
        { name: 'title', label: 'Title' },
        { name: 'description', label: 'Description', type: 'textarea' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            required: true,
            options: [
                { value: 'active', label: 'Active' },
                { value: 'inactive', label: 'Inactive' },
            ],
        },
        { name: 'handle_linkedin', label: 'LinkedIn handle' },
        { name: 'handle_facebook', label: 'Facebook handle' },
        { name: 'handle_instagram', label: 'Instagram handle' },
        { name: 'website_url', label: 'Website URL', type: 'url' },
        { name: 'profile_image_path', label: 'Profile image path' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
    ],
    'content/pamphlet-background-collections': [
        { name: 'name', label: 'Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'content/pamphlet-backgrounds': [
        { name: 'collection_id', label: 'Collection ID', required: true },
        { name: 'name', label: 'Name', required: true },
        { name: 'image_path', label: 'Image path', required: true },
        { name: 'thumbnail_path', label: 'Thumbnail path' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
    ],
    'commerce/subscription-packages': [
        { name: 'name', label: 'Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'price_cents', label: 'Price (cents)', type: 'number', required: true },
        { name: 'currency', label: 'Currency' },
        {
            name: 'billing_interval',
            label: 'Billing interval',
            type: 'select',
            required: true,
            options: [
                { value: 'once_off', label: 'Once off' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'annual', label: 'Annual' },
            ],
        },
        { name: 'is_featured', label: 'Featured', type: 'checkbox' },
        { name: 'is_active', label: 'Active', type: 'checkbox' },
        { name: 'sort_order', label: 'Sort order', type: 'number' },
    ],
    'directory/service-providers': [
        { name: 'name', label: 'Name', required: true },
        { name: 'registration_number', label: 'Registration number' },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'logo_path', label: 'Logo path' },
        { name: 'cover_image_path', label: 'Cover image path' },
        { name: 'email', label: 'Email', type: 'email', required: true },
        { name: 'phone', label: 'Phone' },
        { name: 'website_url', label: 'Website URL', type: 'url' },
        { name: 'physical_address', label: 'Physical address', type: 'textarea' },
        { name: 'city', label: 'City' },
        { name: 'province', label: 'Province' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'pending', label: 'Pending' },
                { value: 'active', label: 'Active' },
                { value: 'suspended', label: 'Suspended' },
            ],
        },
    ],
    'directory/users': [
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email', required: true },
        { name: 'phone', label: 'Phone' },
        { name: 'password', label: 'Password', type: 'password', required: true },
        { name: 'password_confirmation', label: 'Confirm password', type: 'password', required: true },
    ],
    'directory/persons-of-interest': [
        { name: 'first_name', label: 'First name', required: true },
        { name: 'last_name', label: 'Last name', required: true },
        { name: 'display_name', label: 'Display name' },
        { name: 'date_of_birth', label: 'Date of birth', type: 'date', required: true },
        { name: 'date_of_passing', label: 'Date of passing', type: 'date', required: true },
        { name: 'place_of_birth', label: 'Place of birth' },
        { name: 'place_of_passing', label: 'Place of passing' },
        { name: 'profile_image_path', label: 'Profile image path' },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'draft', label: 'Draft' },
                { value: 'active', label: 'Active' },
                { value: 'archived', label: 'Archived' },
            ],
        },
    ],
};

function writeForm(resource, mode) {
    const fields = formFields[resource.pagePath] ?? [{ name: 'name', label: 'Name', required: true }];
    const model = mode === 'edit' ? resource.prop : null;
    const editFields =
        mode === 'edit' && resource.pagePath === 'directory/users'
            ? fields.map((f) =>
                  f.name.startsWith('password') ? { ...f, required: false } : f,
              )
            : fields;
    const fieldsHtml = editFields.map((f) => fieldInput(f, model)).join('\n\n                ');

    const propsType =
        mode === 'edit'
            ? `defineProps<{ ${resource.prop}: Record<string, unknown> }>();`
            : '';

    const formBind = mode === 'create' ? 'v-bind="store.form()"' : `v-bind="update.form(${resource.prop}.id)"`;
    const cancelHref = mode === 'create' ? 'index()' : `show(${resource.prop}.id)`;
    const title = mode === 'create' ? `Create ${resource.singular.toLowerCase()}` : `Edit ${resource.singular.toLowerCase()}`;

    const content = `<script setup lang="ts">
${pageImports(resource, 'form')}
${propsType}
</script>

<template>
    <StaffLayout>
        <Head title="${title}" />

        <StaffPageHeader title="${title}" />

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    ${formBind}
                    class="space-y-6"
                    #default="{ errors, processing }"
                >
                    ${fieldsHtml}

                    <StaffFormActions :cancel-href="${cancelHref}" :processing="processing" />
                </Form>
            </CardContent>
        </Card>
    </StaffLayout>
</template>
`;

    writeFileSync(join(pagesRoot, resource.pagePath, `${mode === 'create' ? 'Create' : 'Edit'}.vue`), content);
}

function writeShow(resource) {
    const extra =
        resource.pagePath === 'marketing/adverts'
            ? `, analytics: { total_visits: number; unique_visits: number }`
            : resource.pagePath === 'marketing/leads'
              ? ''
              : '';

    const leadNotes =
        resource.pagePath === 'marketing/leads'
            ? `
        <Card v-if="lead.notes?.length">
            <CardHeader><CardTitle>Notes</CardTitle></CardHeader>
            <CardContent class="space-y-4">
                <div v-for="note in lead.notes" :key="note.id" class="rounded-lg border border-border p-4">
                    <p class="text-sm whitespace-pre-wrap">{{ note.body }}</p>
                    <p class="mt-2 text-muted-foreground text-xs">{{ note.staff_user?.user?.name }}</p>
                    <Form v-bind="destroyNote.form(lead.id, note.id)" class="mt-2">
                        <Button type="submit" variant="destructive" size="sm">Delete note</Button>
                    </Form>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader><CardTitle>Add note</CardTitle></CardHeader>
            <CardContent>
                <Form v-bind="storeNote.form(lead.id)" class="space-y-4" #default="{ errors, processing }">
                    <div class="grid gap-2">
                        <Label for="body">Note</Label>
                        <Textarea id="body" name="body" rows="4" required />
                        <InputError :message="errors.body" />
                    </div>
                    <StaffFormActions :cancel-href="show(lead.id)" :processing="processing" submit-label="Add note" />
                </Form>
            </CardContent>
        </Card>`
            : '';

    const advertAnalytics =
        resource.pagePath === 'marketing/adverts'
            ? `
        <div class="grid gap-4 sm:grid-cols-2">
            <Card>
                <CardHeader><CardTitle>Total visits</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl">{{ analytics.total_visits }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Unique visits</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl">{{ analytics.unique_visits }}</p></CardContent>
            </Card>
        </div>`
            : '';

    const content = `<script setup lang="ts">
${pageImports(resource, 'show')}
${resource.pagePath === 'marketing/leads' ? `import InputError from '@/components/InputError.vue';\nimport StaffFormActions from '@/components/staff/StaffFormActions.vue';\nimport { Label } from '@/components/ui/label';\nimport { Textarea } from '@/components/ui/textarea';\nimport { store as storeNote, destroy as destroyNote } from '@/routes/staff/marketing/leads/notes';\n` : ''}
const props = defineProps<{
    ${resource.prop}: Record<string, any>${extra}
}>();
</script>

<template>
    <StaffLayout>
        <Head title="${resource.singular}" />

        <StaffPageHeader
            :title="String(props.${resource.prop}.title ?? props.${resource.prop}.name ?? props.${resource.prop}.client_name ?? '${resource.singular}')"
            ${resource.readOnly ? '' : ':actions="[{ label: \'Edit\', href: edit(props.' + resource.prop + '.id) }]"'}
        />
        ${advertAnalytics}

        <Card>
            <CardHeader><CardTitle>Details</CardTitle></CardHeader>
            <CardContent>
                <dl class="grid gap-3 sm:grid-cols-2">
                    <template v-for="(value, key) in props.${resource.prop}" :key="key">
                        <div
                            v-if="value !== null && typeof value !== 'object'"
                            class="rounded-lg border border-border p-3"
                        >
                            <dt class="text-muted-foreground text-xs uppercase">{{ key }}</dt>
                            <dd class="mt-1 text-sm break-words">{{ value }}</dd>
                        </div>
                    </template>
                </dl>
            </CardContent>
        </Card>
        ${leadNotes}

        <div class="flex flex-wrap gap-2">
            <Button variant="outline" as-child>
                <Link :href="index()">Back to list</Link>
            </Button>
            <Form v-if="!${resource.readOnly}" v-bind="destroy.form(props.${resource.prop}.id)">
                <Button type="submit" variant="destructive">Delete</Button>
            </Form>
        </div>
    </StaffLayout>
</template>
`;

    writeFileSync(join(pagesRoot, resource.pagePath, 'Show.vue'), content);
}

for (const resource of resources.filter((r) => r.pagePath)) {
    mkdirSync(join(pagesRoot, resource.pagePath), { recursive: true });
    writeIndex(resource);

    if (!resource.readOnly) {
        writeForm(resource, 'create');
        writeForm(resource, 'edit');
    }

    writeShow(resource);
}

writeFileSync(
    join(pagesRoot, 'Dashboard.vue'),
    `<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StaffPageHeader from '@/components/staff/StaffPageHeader.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StaffLayout from '@/layouts/staff/StaffLayout.vue';
import { index as leadsIndex } from '@/routes/staff/marketing/leads';
import { index as transactionsIndex } from '@/routes/staff/commerce/transactions';
import { index as usersIndex } from '@/routes/staff/directory/users';
import type { StaffUser } from '@/types/staff';

defineProps<{
    counts: {
        users: number;
        persons_of_interest: number;
        memorial_pages: number;
        marketing_leads: number;
        transactions: number;
        service_providers: number;
    };
    staffUser: StaffUser;
}>();
</script>

<template>
    <StaffLayout>
        <Head title="Staff dashboard" />

        <StaffPageHeader
            title="Staff dashboard"
            :description="'Welcome back, ' + staffUser.user.name + '.'"
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Card>
                <CardHeader><CardTitle>Users</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.users }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Persons of interest</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.persons_of_interest }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Memorial pages</CardTitle></CardHeader>
                <CardContent><p class="font-display text-3xl text-brand">{{ counts.memorial_pages }}</p></CardContent>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>New marketing leads</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.marketing_leads }}</p>
                    <Link :href="leadsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View leads</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Completed transactions</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-gold">{{ counts.transactions }}</p>
                    <Link :href="transactionsIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View transactions</Link>
                </CardContent>
            </Card>
            <Card>
                <CardHeader><CardTitle>Service providers</CardTitle></CardHeader>
                <CardContent>
                    <p class="font-display text-3xl text-brand">{{ counts.service_providers }}</p>
                    <Link :href="usersIndex()" class="mt-2 inline-block text-brand text-sm hover:underline">View users</Link>
                </CardContent>
            </Card>
        </div>
    </StaffLayout>
</template>
`,
);

console.log('Generated staff frontend files');
