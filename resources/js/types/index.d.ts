export interface IUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
}

export interface IChat {
    id: number;
    title: string | null;
    users_ids: string;
    messages: IMessage[];
    unreadable_count: number;
    last_message?: IMessage;
}

export interface IMessage {
    id: number;
    chat_id: number;
    user_name: string;
    body: string;
    time: string;
    is_owner: boolean;
    user_id: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: IUser;
    };
};
