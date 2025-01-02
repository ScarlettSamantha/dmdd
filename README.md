# DMDD

## Choices

> Q: Why choose for both python and php
>
> > A: because php does not do threading (well), so its way easier to just have a python daemon running and doing some RPC.

> Q: Why is it split up the way it is
>
> > A: Mostly because of the demo assignment thing but also because the subject matter the application deal with and most of the core logic needing to be detachable to defere liability and it keeps the core re-usable for other projects later on.

> Q: If this is a demo assignment what does it do ?
>
> > A: Its half meant to be a semi framework implementation and extension of the frameworks. But also be able to be re-used but more concretely its meant to download media from plugin based sources.

> Q: My Ide's is having issues
>
> > A: Jetbrains IDE's dont like mixing languages that they sell other ide's to, so you can get:
> >
> > - VSCode (pref) - Setup VSCode which is my prefered options now a days. As languages are just a plugin and not integrated into the core as tightly. You will need a lot of extensions tough and its a bit of setup work to get everything to interact properly and to link every plugin correctly + config.
> > - Alone - Setup both ide's seperate as they are both contained in their own subfolders they can be loaded seperately.
> > - Link - Setup cross language linking in your jetbrains IDE (with plugins) should be able to handle this.
