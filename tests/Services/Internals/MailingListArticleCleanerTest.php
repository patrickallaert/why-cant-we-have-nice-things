<?php
namespace History\Services\Internals;

use History\TestCase;

class MailingListArticleCleanerTest extends TestCase
{
    public function testCanParseArticleFromMailingList()
    {
        $article = <<<'TEXT'
Newsgroups: php.internals
Path: news.php.net
Xref: news.php.net php.internals:88938
Return-Path: <php@tutteli.ch>
Mailing-List: contact internals-help@lists.php.net; run by ezmlm
Delivered-To: mailing list internals@lists.php.net
Received: (qmail 4594 invoked from network); 26 Oct 2015 10:10:56 -0000
Received: from unknown (HELO lists.php.net) (127.0.0.1)
  by localhost with SMTP; 26 Oct 2015 10:10:56 -0000
Authentication-Results: pb1.pair.com smtp.mail=php@tutteli.ch; spf=fail; sender-id=fail
Authentication-Results: pb1.pair.com header.from=php@tutteli.ch; sender-id=fail
Received-SPF: fail (pb1.pair.com: domain tutteli.ch does not designate 80.74.154.80 as permitted sender)
X-PHP-List-Original-Sender: php@tutteli.ch
X-Host-Fingerprint: 80.74.154.80 hyperion2.kreativmedia.ch Linux 2.6
Received: from [80.74.154.80] ([80.74.154.80:41467] helo=hyperion2.kreativmedia.ch)
	by pb1.pair.com (ecelerity 2.1.1.9-wez r(12769M)) with ESMTP
	id 34/50-02627-E2CFD265 for <internals@lists.php.net>; Mon, 26 Oct 2015 05:10:56 -0500
Received: from RoLaptop (adsl-84-226-55-217.adslplus.ch [84.226.55.217])
	by hyperion2.kreativmedia.ch (Postfix) with ESMTPSA id B82DBDCE809A;
	Mon, 26 Oct 2015 11:10:50 +0100 (CET)
To: "'Larry Garfield'" <larry@garfieldtech.com>,
	<internals@lists.php.net>
References: <0A.C2.33697.6AECE165@pb1.pair.com> <11.10.09496.8F410265@pb1.pair.com> <001b01d107f1$9d672370$d8356a50$@tutteli.ch> <CAAyV7nG9UyDr1AA5xEK1DYnQ7mLrX95ZNWoZWp421KuUscBmYA@mail.gmail.com> <004101d10844$55f89d90$01e9d8b0$@tutteli.ch> <562AB638.1090304@garfieldtech.com>
In-Reply-To: <562AB638.1090304@garfieldtech.com>
Date: Mon, 26 Oct 2015 11:10:47 +0100
Message-ID: <00e801d10fd6$95b3c6a0$c11b53e0$@tutteli.ch>
MIME-Version: 1.0
Content-Type: text/plain;
	charset="UTF-8"
Content-Transfer-Encoding: quoted-printable
X-Mailer: Microsoft Outlook 14.0
Thread-Index: AQIlm6HitWabnDu1qXRDdKGyTlYfFAGnR/YjAXC9KW0A7mbEgQH3VZCOAjVnauCdktTSYA==
Content-Language: de-ch
Subject: =?UTF-8?Q?AW:_AW:_=5BPHP-DEV=5D_Re:_=5BRFC=5D_Void?=
	=?UTF-8?Q?_Return_Type_=28v0.2=2C_re=C3=B6pening=29?=
From: php@tutteli.ch ("Robert Stoll")

Hi Larry,

> That wouldn't help either, I think.  Then you'd need a separate=
partialLeft(callable:int $cb), partialLeft(callable:string $cb),
> partialLeft(callable:float $cb), and partialLeft(callable:void $db).=
And likely others. That seems exactly like what Anthony
> wants to avoid (rightly).
>
> Indirect calls to arbitrary functions does mean that they need to be=
able to behave consistently when referred to
> abstractly.  Vis, any approach that involves:
>
> function foo() : void {}
> $a =3D foo();
>
> triggering an error condition would make life drastically more=
difficult for higher order function operations like partials or
> memoization.  That seems doubleplusungood.
>
> One way around that would be to only trigger that behavior on a static=
call, not a call to a variable function, but I have no
> idea if that's at all feasible in the engine.  I suspect it's more=
feasible than detecting the function wrapping and only erroring
> at the top level caller, but now I'm just talking out of my butt. :-)
>
> That leaves "documentation of intent for the developer" (which is a=
valid argument) and "slap someone's hand for
> returning non-null inside the function itself" (which is valid, but=
leaves the question of whether return null should error).
>
> --Larry Garfield
>
> --

Well, it really depends on the use case. I would probably not write such=
a generic partial function, especially not allowing functions which=
return a value and others which don't. I could imagine to use=
callable:mixed to allow values of arbitrary types to be returned. Yet,=
that would still not include a function which does not return a value.=
IMO we do not really proceed further with this RFC when discussing this=
special case - callable:int, callable:mixed etc is not supported by PHP=
now, so we should focus on the essential now.

IMO we should get an agreement what void means in PHP, I see the=
following options:

1 void is a type with a value set containing null (ergo corresponds to=
the set { null }) and hence it is perfectly fine to return null from a=
function with return type void (naming it void is controversial among=
the list -- others prefer null -- but that can be discussed further=
afterwards)
2 void is a type with an empty value set (a special value respectively)=
and hence one cannot return null from a function with return type void=
and
  a) such a function returns null implicitly nonetheless
  b) such a function returns a special value which triggers a fatal=
error when it is accessed

IMO 2a is inconsistent and I would only consider 1 or 2b

.
TEXT;
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $article));

        $this->assertEquals(<<<'TEXT'
Hi Larry,

> That wouldn't help either, I think.  Then you'd need a separate
partialLeft(callable:int $cb), partialLeft(callable:string $cb),
> partialLeft(callable:float $cb), and partialLeft(callable:void $db).
And likely others. That seems exactly like what Anthony
> wants to avoid (rightly).
>
> Indirect calls to arbitrary functions does mean that they need to be
able to behave consistently when referred to
> abstractly.  Vis, any approach that involves:
>
> function foo() : void {}
> $a = foo();
>
> triggering an error condition would make life drastically more
difficult for higher order function operations like partials or
> memoization.  That seems doubleplusungood.
>
> One way around that would be to only trigger that behavior on a static
call, not a call to a variable function, but I have no
> idea if that's at all feasible in the engine.  I suspect it's more
feasible than detecting the function wrapping and only erroring
> at the top level caller, but now I'm just talking out of my butt. :-)
>
> That leaves "documentation of intent for the developer" (which is a
valid argument) and "slap someone's hand for
> returning non-null inside the function itself" (which is valid, but
leaves the question of whether return null should error).
>
> --Larry Garfield
>
> --

Well, it really depends on the use case. I would probably not write such
a generic partial function, especially not allowing functions which
return a value and others which don't. I could imagine to use
callable:mixed to allow values of arbitrary types to be returned. Yet,
that would still not include a function which does not return a value.
IMO we do not really proceed further with this RFC when discussing this
special case - callable:int, callable:mixed etc is not supported by PHP
now, so we should focus on the essential now.

IMO we should get an agreement what void means in PHP, I see the
following options:

1 void is a type with a value set containing null (ergo corresponds to
the set { null }) and hence it is perfectly fine to return null from a
function with return type void (naming it void is controversial among
the list -- others prefer null -- but that can be discussed further
afterwards)
2 void is a type with an empty value set (a special value respectively)
and hence one cannot return null from a function with return type void
and
  a) such a function returns null implicitly nonetheless
  b) such a function returns a special value which triggers a fatal
error when it is accessed

IMO 2a is inconsistent and I would only consider 1 or 2b
TEXT
, $cleaned);
    }
}
