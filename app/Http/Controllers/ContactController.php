<?php

namespace App\Http\Controllers;

use App\Actions\Messages\ReceiveMessageAction;
use App\Actions\Pages\LoadPageAction;
use App\Enums\Messages\ContactTopicEnum;
use App\Enums\Messages\MessageTypeEnum;
use App\Enums\Pages\PageEnum;
use App\Http\Requests\Site\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Kontaktformular – mit ?thema=booking ist das Thema vorausgewählt.
     */
    public function show(Request $request, LoadPageAction $loadPage): View
    {
        return view('pages.contact', [
            'texts' => $loadPage->execute(PageEnum::Contact)->content,
            'topic' => ContactTopicEnum::tryFrom((string) $request->query('thema')) ?? ContactTopicEnum::General,
        ]);
    }

    public function store(ContactRequest $request, ReceiveMessageAction $receiveMessage): RedirectResponse
    {
        $receiveMessage->execute(MessageTypeEnum::Contact, $request->messageAttributes());

        return to_route('contact')->withFragment('formular')->with('sent', true);
    }
}
