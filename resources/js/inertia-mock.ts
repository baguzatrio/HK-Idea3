import { reactive } from 'vue';

export const useForm = (data: any = {}) => {
    return reactive({
        ...data,
        processing: false,
        errors: {},
        recentlySuccessful: false,
        post: (url: string, options?: any) => {},
        put: (url: string, options?: any) => {},
        delete: (url: string, options?: any) => {},
        patch: (url: string, options?: any) => {},
        reset: (...fields: string[]) => {},
        clearErrors: (...fields: string[]) => {},
    });
};

export const usePage = () => ({
    props: { auth: { user: { name: '', email: '', email_verified_at: '' } } }
});

